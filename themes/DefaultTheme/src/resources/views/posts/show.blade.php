@extends('front::layouts.master', ['title' => $post->meta_title ?: $post->title])

@push('meta')
    @if(option('allow_indexing_blog_page') =="off" )
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="bingbot" content="noindex, nofollow">

    @else
    <meta name="robots" content="index, follow"/>
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
    @endif
    <meta property="og:title" content="{{ $post->meta_title ?: $post->title }}" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="{{ route('front.posts.show', ['post' => $post]) }}" />
    <meta name="description" content="{{ $post->meta_description ?: $post->short_description }}">
    <meta name="keywords" content="{{ $post->getTags }}">
    <link rel="canonical" href="{{ route('front.posts.show', ['post' => $post]) }}" />

    @if ($post->image)
        <meta property="og:image" content="{{ asset($post->image) }}">
    @endif

@endpush

@section('content')
    {{-- ARIYA-ARTICLE-POLISH-V1 --}}
    <style>
        /*
         * Dedicated article-page fixes.
         * Everything is scoped to .ariya-article-page so product/category
         * pages and the rest of the storefront remain untouched.
         */

        .ariya-article-page {
            --article-primary: #0a8acb;
            --article-primary-dark: #0875ad;
            --article-text: #273444;
            --article-muted: #7a8794;
            --article-border: #e7edf3;
            --article-soft: #f7fafc;

            float: none !important;
            width: 100%;
            height: auto !important;
            clear: both;
        }

        .ariya-article-page .main-container {
            width: 100%;
        }

        /* ---------- Breadcrumb + page title ---------- */

        .ariya-article-page .title-breadcrumb-special,
        .ariya-article-page .title-breadcrumb-special .breadcrumb,
        .ariya-article-page .title-breadcrumb-special .title-page {
            float: none !important;
            width: 100%;
            height: auto !important;
        }

        .ariya-article-page .title-breadcrumb-special {
            margin-bottom: 18px;
        }

        .ariya-article-page .title-breadcrumb-special .breadcrumb {
            margin-bottom: 6px;
        }

        .ariya-article-page .title-breadcrumb-special .breadcrumb nav {
            line-height: 1.9;
        }

        .ariya-article-page .title-breadcrumb-special .title-page h1 {
            margin: 0 !important;
            color: #1f2d3d;
            font-size: clamp(20px, 2vw, 27px) !important;
            font-weight: 700;
            line-height: 1.8;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        /* ---------- Main article flow ---------- */

        .ariya-article-page .ariya-article-main-row {
            align-items: stretch;
        }

        .ariya-article-page .content-page,
        .ariya-article-page .content-desc {
            float: none !important;
            width: 100% !important;
            height: auto !important;
            min-height: 0 !important;
        }

        .ariya-article-page .content-page {
            display: flow-root;
        }

        .ariya-article-page .content-desc {
            display: flow-root;
            padding: 14px;
            border: 1px solid var(--article-border);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 3px 16px rgba(31, 45, 61, .045);
        }

        .ariya-article-page .entry-header {
            float: none !important;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 7px 15px;
            width: 100% !important;
            height: auto !important;
            margin: 0 0 14px !important;
            padding: 0 2px 12px;
            border-bottom: 1px solid #eef2f5;
        }

        .ariya-article-page .content-desc .post-meta {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding-right: 0 !important;
            color: var(--article-muted);
            font-size: 11.5px;
            line-height: 1.8;
        }

        .ariya-article-page .content-desc .post-meta a {
            color: inherit;
        }

        /* ---------- Featured image ---------- */

        .ariya-article-page .post-thumbnail {
            float: none !important;
            display: block;
            width: 100% !important;
            height: auto !important;
            margin: 0 0 20px !important;
            overflow: hidden;
            border: 1px solid #edf1f5;
            border-radius: 14px;
            background: #f5f7fa;
        }

        .ariya-article-page .post-thumbnail img {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            object-fit: cover;
        }

        /* ---------- Article body ---------- */

        .ariya-article-page .ariya-article-body {
            float: none !important;
            display: flow-root;
            width: 100%;
            min-width: 0;
            margin-top: 18px !important;
            padding: 0;
            color: var(--article-text);
        }

        .ariya-article-page .ariya-article-body img,
        .ariya-article-page .ariya-article-body iframe,
        .ariya-article-page .ariya-article-body video {
            max-width: 100%;
        }

        .ariya-article-page .ariya-article-body img {
            height: auto;
        }

        /*
         * Some old article bodies contain a literal placeholder image.
         * It is not a real URL, so never reserve a broken-image box for it.
         */
        .ariya-article-page
        .ariya-article-body
        img[src="آدرس-تصویر-شاخص-اینجا"] {
            display: none !important;
        }

        /* ---------- Legacy AJ article design normalization ---------- */

        .ariya-article-page .ariya-article-body .aj-blog {
            width: 100%;
            max-width: 100%;
            margin: 0;
            color: var(--article-text) !important;
            font-family: inherit !important;
            font-size: 14.5px !important;
            line-height: 2.05 !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog .aj-hero {
            margin: 0 0 20px !important;
            overflow: hidden;
            border: 1px solid var(--article-border) !important;
            border-radius: 14px !important;
            background: #fff;
            box-shadow: none !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog .aj-hero-content {
            padding: 24px !important;
            color: #fff !important;
            background: linear-gradient(
                135deg,
                #075b8b 0%,
                var(--article-primary) 100%
            ) !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog h1 {
            margin: 0 0 8px !important;
            color: #fff !important;
            font-size: 23px !important;
            font-weight: 700;
            line-height: 1.75 !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog h2 {
            margin: 0 0 12px !important;
            color: #203246 !important;
            font-size: 19px !important;
            font-weight: 700;
            line-height: 1.9;
        }

        .ariya-article-page .ariya-article-body .aj-blog h3 {
            margin: 20px 0 8px;
            color: #294158;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.9;
        }

        .ariya-article-page .ariya-article-body .aj-blog p {
            margin: 10px 0 !important;
            color: #465666;
            line-height: 2.15 !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog .aj-card {
            margin: 16px 0 !important;
            padding: 22px !important;
            border: 1px solid var(--article-border) !important;
            border-radius: 13px !important;
            background: #fff !important;
            box-shadow: 0 3px 14px rgba(31, 45, 61, .035) !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog .aj-note {
            margin: 15px 0 !important;
            padding: 15px 16px !important;
            border: 1px solid #dcecf5 !important;
            border-right: 3px solid var(--article-primary) !important;
            border-radius: 10px !important;
            background: #f7fbfe !important;
            color: #3d5264;
        }

        .ariya-article-page .ariya-article-body .aj-blog .aj-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 12px !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog .aj-item {
            padding: 16px !important;
            border: 1px solid #edf1f4;
            border-radius: 10px !important;
            background: #f9fbfc !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog ul {
            margin: 12px 0 !important;
            padding-right: 22px !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog ul li {
            margin-bottom: 7px !important;
            list-style: disc !important;
            color: #465666;
            line-height: 2;
        }

        .ariya-article-page .ariya-article-body .aj-blog table {
            width: 100% !important;
            margin: 15px 0 0 !important;
            border-collapse: collapse !important;
            border-spacing: 0;
            background: #fff;
        }

        .ariya-article-page .ariya-article-body .aj-blog th,
        .ariya-article-page .ariya-article-body .aj-blog td {
            padding: 11px 10px !important;
            border: 1px solid #e5ebf0 !important;
            text-align: center;
            vertical-align: middle;
        }

        .ariya-article-page .ariya-article-body .aj-blog th {
            color: #fff !important;
            background: #223b55 !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog .aj-accordion {
            margin: 10px 0 !important;
            overflow: hidden;
            border: 1px solid #e4ebf1 !important;
            border-radius: 10px !important;
            background: #fff;
        }

        .ariya-article-page .ariya-article-body .aj-blog label {
            position: relative;
            margin: 0;
            padding: 14px 15px !important;
            color: #2c4053;
            background: #fbfcfd !important;
            font-size: 13px;
            line-height: 1.9;
        }

        .ariya-article-page .ariya-article-body .aj-blog label::before {
            color: var(--article-primary) !important;
            font-size: 19px !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog .aj-answer {
            padding-right: 16px !important;
            padding-left: 16px !important;
        }

        .ariya-article-page
        .ariya-article-body
        .aj-blog
        input:checked ~ .aj-answer {
            max-height: 600px !important;
            padding-top: 12px !important;
            padding-bottom: 12px !important;
            border-top: 1px solid #eef2f5;
        }

        .ariya-article-page .ariya-article-body .aj-blog .aj-cta {
            margin-top: 20px !important;
            padding: 24px !important;
            border-radius: 13px !important;
            color: #fff !important;
            background: linear-gradient(
                135deg,
                #075b8b,
                var(--article-primary)
            ) !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog .aj-cta h2,
        .ariya-article-page .ariya-article-body .aj-blog .aj-cta p {
            color: #fff !important;
        }

        .ariya-article-page .ariya-article-body .aj-blog .aj-btn {
            margin-top: 10px !important;
            padding: 9px 20px !important;
            border: 1px solid rgba(255, 255, 255, .45);
            border-radius: 8px !important;
            color: #075b8b !important;
            background: #fff !important;
            font-size: 12.5px;
            font-weight: 600;
        }

        /* ---------- Latest posts sidebar ---------- */

        @media (min-width: 992px) {
            .ariya-article-page .ariya-article-sidebar {
                position: relative !important;
                align-self: stretch !important;
                overflow: visible !important;
            }

            .ariya-article-page .ariya-article-sidebar__sticky {
                position: -webkit-sticky !important;
                position: sticky !important;
                top: 20px !important;
                float: none !important;
                display: flow-root !important;
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 12px !important;
                transform: none !important;
                border: 1px solid var(--article-border);
                border-radius: 13px;
                background: #fff;
                box-shadow: 0 3px 15px rgba(31, 45, 61, .04);
            }
        }

        .ariya-article-page .ariya-article-sidebar__sticky .title-sidebar,
        .ariya-article-page .ariya-article-sidebar__sticky .content-sidebar {
            float: none !important;
            width: 100% !important;
            height: auto !important;
        }

        .ariya-article-page .ariya-article-sidebar__sticky .title-sidebar {
            margin: 0 0 8px !important;
            padding: 0 0 9px;
            border-bottom: 1px solid #edf1f4;
        }

        .ariya-article-page
        .ariya-article-sidebar__sticky
        .title-sidebar
        h3 {
            margin: 0;
            color: #24384b;
            font-size: 14px;
            line-height: 1.8;
        }

        .ariya-article-page
        .ariya-article-sidebar__sticky
        .content-sidebar
        .item {
            float: none !important;
            width: 100%;
            height: auto !important;
            margin: 0 !important;
            padding: 10px 0 !important;
            border-bottom: 1px solid #f0f3f6;
        }

        .ariya-article-page
        .ariya-article-sidebar__sticky
        .content-sidebar
        .item:last-child {
            padding-bottom: 0 !important;
            border-bottom: 0;
        }

        .ariya-article-page
        .ariya-article-sidebar__sticky
        .item-inner {
            display: grid;
            grid-template-columns: 72px minmax(0, 1fr);
            grid-template-rows: auto auto;
            gap: 2px 9px;
            align-items: start;
            width: 100%;
            min-width: 0;
        }

        .ariya-article-page
        .ariya-article-sidebar__sticky
        .item-thumb {
            grid-column: 1;
            grid-row: 1 / 3;
            float: none !important;
            width: 72px;
            margin: 0 !important;
        }

        .ariya-article-page
        .ariya-article-sidebar__sticky
        .item-thumb
        a {
            display: block !important;
            width: 72px !important;
            height: 52px !important;
            margin: 0 !important;
            border-radius: 7px !important;
            background-position: center;
            background-size: cover;
        }

        .ariya-article-page
        .ariya-article-sidebar__sticky
        p.title {
            grid-column: 2;
            grid-row: 1;
            min-width: 0;
            margin: 0 !important;
        }

        .ariya-article-page
        .ariya-article-sidebar__sticky
        p.title
        a {
            display: -webkit-box;
            overflow: hidden;
            color: #344657 !important;
            font-size: 11.5px !important;
            font-weight: 500 !important;
            line-height: 1.8 !important;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .ariya-article-page
        .ariya-article-sidebar__sticky
        .item-meta {
            grid-column: 2;
            grid-row: 2;
            margin-top: 2px;
            color: #9aa5af !important;
            font-size: 9.5px !important;
            line-height: 1.6;
        }

        /* ---------- Comments ---------- */

        .ariya-article-page .ariya-article-comments-row {
            clear: both;
            margin-top: 4px;
        }

        .ariya-article-page .ariya-article-comments-row .section-title,
        .ariya-article-page .ariya-article-comments-row .form-question-answer,
        .ariya-article-page .ariya-article-comments-row .comments-area {
            float: none !important;
            width: 100% !important;
            height: auto !important;
        }

        .ariya-article-page .ariya-article-comments-row .section-title {
            margin-bottom: 9px !important;
        }

        .ariya-article-page .ariya-article-comments-row .section-title h2 {
            margin: 0 0 8px;
            color: #273b4e;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.8;
        }

        .ariya-article-page
        .ariya-article-comments-row
        .form-question-answer {
            padding: 14px;
            border: 1px solid var(--article-border);
            border-radius: 12px;
            background: #fff;
        }

        .ariya-article-page
        .ariya-article-comments-row
        .form-question-answer
        textarea {
            display: block;
            width: 100%;
            min-height: 130px;
            padding: 12px 13px;
            resize: vertical;
            border: 1px solid #dfe6ec;
            border-radius: 9px;
            background: #fff;
            line-height: 1.9;
            box-shadow: none;
        }

        .ariya-article-page
        .ariya-article-comments-row
        .comment-submit-btn {
            float: none !important;
            min-width: 105px;
            margin: 0 !important;
            padding: 9px 15px;
            border-radius: 8px;
        }

        .ariya-article-page .ariya-article-comments-row .comments-area {
            margin-top: 14px;
        }

        .ariya-article-page
        .ariya-article-comments-row
        ol.comment-list {
            margin: 0;
            padding: 0;
        }

        /* ---------- Tablet / mobile ---------- */

        @media (max-width: 991.98px) {
            .ariya-article-page .ariya-article-main-row > .col-lg-9,
            .ariya-article-page .ariya-article-main-row > .ariya-article-sidebar,
            .ariya-article-page .ariya-article-comments-row > .col-lg-9 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .ariya-article-page .ariya-article-sidebar,
            .ariya-article-page .ariya-article-sidebar__sticky {
                position: static !important;
                top: auto !important;
                float: none !important;
                width: 100% !important;
                height: auto !important;
                transform: none !important;
            }

            .ariya-article-page .ariya-article-sidebar__sticky {
                display: flow-root !important;
                margin-top: 6px;
                padding: 12px !important;
                border: 1px solid var(--article-border);
                border-radius: 13px;
                background: #fff;
            }

            .ariya-article-page .ariya-article-comments-row > [aria-hidden="true"] {
                display: none !important;
            }
        }

        @media (max-width: 767.98px) {
            .ariya-article-page {
                margin-top: 14px !important;
            }

            .ariya-article-page .content-desc {
                padding: 10px;
                border-radius: 11px;
            }

            .ariya-article-page .title-breadcrumb-special {
                margin-bottom: 12px;
            }

            .ariya-article-page
            .title-breadcrumb-special
            .title-page
            h1 {
                font-size: 19px !important;
                line-height: 1.85;
            }

            .ariya-article-page .ariya-article-body {
                margin-top: 12px !important;
            }

            .ariya-article-page .ariya-article-body .aj-blog {
                font-size: 13.5px !important;
            }

            .ariya-article-page
            .ariya-article-body
            .aj-blog
            .aj-hero-content {
                padding: 18px !important;
            }

            .ariya-article-page .ariya-article-body .aj-blog h1 {
                font-size: 20px !important;
            }

            .ariya-article-page .ariya-article-body .aj-blog h2 {
                font-size: 17px !important;
            }

            .ariya-article-page .ariya-article-body .aj-blog .aj-card {
                margin: 12px 0 !important;
                padding: 16px !important;
            }

            .ariya-article-page .ariya-article-body .aj-blog .aj-grid {
                grid-template-columns: 1fr !important;
            }

            .ariya-article-page .ariya-article-body .aj-blog table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                white-space: nowrap;
            }

            .ariya-article-page
            .ariya-article-sidebar__sticky
            .item-inner {
                grid-template-columns: 78px minmax(0, 1fr);
            }

            .ariya-article-page
            .ariya-article-sidebar__sticky
            .item-thumb,
            .ariya-article-page
            .ariya-article-sidebar__sticky
            .item-thumb
            a {
                width: 78px !important;
            }
        }

        /* =========================================================
           ARIYA-ARTICLE-POLISH-V2
           Responsive reading width + local post media + final flow
           ========================================================= */

        /*
         * Keep the reading column comfortable even when the article
         * itself occupies the full viewport width.
         */
        .ariya-article-page .entry-header,
        .ariya-article-page .post-thumbnail,
        .ariya-article-page .ariya-article-body {
            max-width: 900px;
            margin-right: auto !important;
            margin-left: auto !important;
        }

        .ariya-article-page .content-desc {
            padding: 16px;
        }

        /*
         * Featured image: real article images keep their natural
         * aspect ratio instead of becoming a fixed/square block.
         */
        .ariya-article-page .post-thumbnail {
            aspect-ratio: auto !important;
            min-height: 0 !important;
        }

        .ariya-article-page .post-thumbnail img {
            aspect-ratio: auto !important;
            object-fit: contain !important;
            background: #f7f8fa;
        }

        /*
         * The article content itself already contains an introductory
         * hero. Make it secondary to the real page H1.
         */
        .ariya-article-page
        .ariya-article-body
        .aj-blog
        .aj-hero-content {
            padding: 20px 22px !important;
        }

        .ariya-article-page
        .ariya-article-body
        .aj-blog
        .aj-hero
        h1 {
            margin-bottom: 6px !important;
            font-size: 19px !important;
            line-height: 1.85 !important;
        }

        .ariya-article-page
        .ariya-article-body
        .aj-blog
        .aj-hero
        p {
            margin: 0 !important;
            font-size: 13px !important;
            line-height: 2 !important;
            opacity: .92;
        }

        /*
         * Slightly calmer editorial cards.
         */
        .ariya-article-page
        .ariya-article-body
        .aj-blog
        .aj-card {
            box-shadow: none !important;
        }

        .ariya-article-page
        .ariya-article-body
        .aj-blog
        .aj-item {
            min-height: 118px;
        }

        /*
         * CTA in this post sits outside .aj-blog in the stored HTML.
         * Style it based on the article wrapper rather than depending
         * on .aj-blog ancestry.
         */
        .ariya-article-page
        .ariya-article-body
        .aj-cta {
            margin: 18px 0 0 !important;
            padding: 24px !important;
            border-radius: 13px !important;
            color: #fff !important;
            text-align: center;
            background: linear-gradient(
                135deg,
                #075b8b 0%,
                #0a8acb 100%
            ) !important;
            box-shadow: none !important;
        }

        .ariya-article-page
        .ariya-article-body
        .aj-cta
        h2 {
            margin: 0 0 8px !important;
            color: #fff !important;
            font-size: 19px !important;
            line-height: 1.8;
        }

        .ariya-article-page
        .ariya-article-body
        .aj-cta
        p {
            margin: 0 !important;
            color: rgba(255, 255, 255, .9) !important;
            line-height: 2;
        }

        .ariya-article-page
        .ariya-article-body
        .aj-cta
        .aj-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            margin-top: 12px !important;
            padding: 8px 20px !important;
            border: 1px solid rgba(255, 255, 255, .55);
            border-radius: 8px !important;
            color: #086d9f !important;
            background: #fff !important;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
        }

        /*
         * Comment block follows the same reading width as the article.
         */
        .ariya-article-page
        .ariya-article-comments-row
        > .col-xl-9
        > .dt-sl {
            float: none !important;
            width: 100%;
            max-width: 900px;
            margin-right: auto !important;
            margin-left: auto !important;
        }

        /*
         * Bootstrap LG at ~1000px is too narrow for a readable
         * 9/3 article layout. Stack until a true desktop width.
         */
        @media (max-width: 1199.98px) {

            .ariya-article-page
            .ariya-article-main-row
            > .col-xl-9,

            .ariya-article-page
            .ariya-article-main-row
            > .ariya-article-sidebar,

            .ariya-article-page
            .ariya-article-comments-row
            > .col-xl-9 {
                flex: 0 0 100% !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .ariya-article-page .ariya-article-sidebar {
                position: static !important;
                align-self: auto !important;
                height: auto !important;
                margin-top: 4px;
                overflow: visible !important;
            }

            .ariya-article-page
            .ariya-article-sidebar__sticky {
                position: static !important;
                top: auto !important;
                float: none !important;
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                transform: none !important;
            }

            /*
             * Latest posts becomes a compact two-column section
             * below the article instead of stealing reading width.
             */
            .ariya-article-page
            .ariya-article-sidebar__sticky
            .content-sidebar {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                column-gap: 20px;
            }

            .ariya-article-page
            .ariya-article-sidebar__sticky
            .content-sidebar
            .item {
                min-width: 0;
            }

            .ariya-article-page
            .ariya-article-comments-row
            > [aria-hidden="true"] {
                display: none !important;
            }

            .ariya-article-page
            .ariya-article-comments-row {
                width: 100%;
            }
        }

        /*
         * True desktop: sidebar is allowed to stay beside the article.
         */
        @media (min-width: 1200px) {
            .ariya-article-page .ariya-article-sidebar {
                position: relative !important;
                align-self: stretch !important;
                overflow: visible !important;
            }

            .ariya-article-page
            .ariya-article-sidebar__sticky {
                position: -webkit-sticky !important;
                position: sticky !important;
                top: 20px !important;
                height: auto !important;
                transform: none !important;
            }
        }

        @media (max-width: 767.98px) {

            .ariya-article-page .content-desc {
                padding: 10px;
            }

            .ariya-article-page
            .ariya-article-sidebar__sticky
            .content-sidebar {
                display: block;
            }

            .ariya-article-page
            .ariya-article-body
            .aj-blog
            .aj-item {
                min-height: 0;
            }

            .ariya-article-page
            .ariya-article-body
            .aj-cta {
                padding: 18px !important;
            }

            .ariya-article-page
            .ariya-article-body
            .aj-cta
            h2 {
                font-size: 17px !important;
            }
        }
    </style>

    <!-- Start main-content -->
    <main class="main-content dt-sl mt-4 mb-3 ariya-article-page">
        <div class="container main-container">

            <div class="row">
                <div class="col-12">
                    <!-- Start title - breadcrumb -->
                    <div class="title-breadcrumb-special dt-sl">
                        <div class="breadcrumb dt-sl">
                            <nav>
                                <a href="/">{{ trans('front::messages.posts.home') }}</a>
                                <a href="{{ route('front.posts.index') }}">{{ trans('front::messages.posts.blog') }}</a>
                                <a href="#">{{ $post->title }}</a>
                            </nav>
                        </div>
                        <div class="title-page dt-sl pb-3">
                            <h1>{{ $post->title }}</h1>
                        </div>

                    </div>
                    <!-- End title - breadcrumb -->
                </div>
            </div>

            {{-- Article body + sticky latest posts --}}
            <div class="row ariya-article-main-row">
                <div class="col-xl-9 col-12 mb-3">
                    <div class="content-page">
                        <div class="content-desc dt-sn dt-sl ariya-post-layout-v1">
                            <header class="entry-header dt-sl mb-3">
                                <div class="post-meta date">
                                    <i class="mdi mdi-calendar-month"></i>{{ jdate($post->created_at)->format('%d %B %Y') }}
                                </div>

                                @if($post->category)
                                    <div class="post-meta category">
                                        <i class="mdi mdi-folder"></i>

                                        <a href="{{ route('front.posts.category', ['category' => $post->category]) }}">{{ $post->category->title }}</a>
                                    </div>
                                @endif

                                <div class="post-meta category">
                                    <i class="mdi mdi-eye"></i>
                                    {{ $post->view }} {{ trans('front::messages.posts.visit') }}
                                </div>
                            </header>

                            @if($post->image)
                                <div class="post-thumbnail dt-sl ariya-post-featured-v1">
                                    <img class="w-100" src="{{ asset($post->image) }}" data-src="{{ asset($post->image) }}" alt="{{ $post->title }}" loading="eager">
                                </div>
                            @endif

                            <div class="col-12 mt-4 ariya-post-article-v1">
                                {!! $post->content !!}
                            </div>
                        </div>
                    </div>
                </div>

                @include('front::posts.partials.sidebar', ['native_sticky' => true])
            </div>

            {{-- Comments start after the article/sidebar row --}}
            <div class="row ariya-article-comments-row">
                <div class="col-xl-9 col-12 mb-3">
                    <div class="dt-sl mt-3">
                        @include('front::components.comments', ['model' => $post, 'route_link' => route('front.post.comments', ['post' => $post]) ])
                    </div>
                </div>

                <div class="col-xl-3 d-none d-xl-block" aria-hidden="true"></div>
            </div>

        </div>
    </main>
    <!-- End main-content -->
@endsection

@push('scripts')
    <script src="{{ theme_asset('js/pages/comments.js') }}"></script>
@endpush


{{-- ARIYA_POST_IMAGE_LAYOUT_V1 --}}
@push('styles')
<style>
/* Global post-detail layout: image first, article second. */

.content-desc.ariya-post-layout-v1 {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    align-items: start !important;
    gap: 24px !important;
    width: 100% !important;
    min-width: 0 !important;
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    overflow: visible !important;
    box-sizing: border-box !important;
}

/* Article metadata must not overlap the cover. */
.ariya-post-layout-v1 > .entry-header {
    grid-column: 1 !important;
    position: relative !important;
    float: none !important;
    width: 100% !important;
    margin: 0 !important;
    height: auto !important;
    min-height: 0 !important;
}

/* Standard 16:9 featured-image box. */
.ariya-post-layout-v1 > .ariya-post-featured-v1 {
    grid-column: 1 !important;
    display: grid !important;
    place-items: center !important;
    position: relative !important;
    inset: auto !important;
    float: none !important;
    clear: both !important;
    width: 100% !important;
    min-width: 0 !important;
    height: auto !important;
    min-height: 0 !important;
    max-height: 420px !important;
    aspect-ratio: 16 / 9;
    padding: 0 !important;
    margin: 0 !important;
    overflow: hidden !important;
    border-radius: 18px !important;
    border: 1px solid #e5eaf3 !important;
    background: #f5f7fb !important;
    box-sizing: border-box !important;
    transform: none !important;
    z-index: 0 !important;
}

/* Show the complete image without cropping. */
.ariya-post-layout-v1 > .ariya-post-featured-v1 > img {
    display: block !important;
    position: static !important;
    width: 100% !important;
    height: 100% !important;
    min-width: 0 !important;
    min-height: 0 !important;
    max-width: 100% !important;
    max-height: 420px !important;
    padding: 0 !important;
    margin: 0 !important;
    object-fit: contain !important;
    object-position: center !important;
    transform: none !important;
}

/* Article content is a separate row BELOW the image. */
.ariya-post-layout-v1 > .ariya-post-article-v1 {
    grid-column: 1 !important;
    grid-row: auto !important;
    display: flow-root !important;
    position: relative !important;
    inset: auto !important;
    float: none !important;
    clear: both !important;
    width: 100% !important;
    min-width: 0 !important;
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
    transform: none !important;
    box-sizing: border-box !important;
    z-index: 0 !important;
}

/* Keep custom article templates inside their own column. */
.ariya-post-article-v1 > article,
.ariya-post-article-v1 > .ariya-blog-wrap,
.ariya-post-article-v1 > .aj-blog {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
}

@media (max-width: 767.98px) {
    .content-desc.ariya-post-layout-v1 {
        gap: 16px !important;
    }

    .ariya-post-layout-v1 > .ariya-post-featured-v1 {
        max-height: 260px !important;
        border-radius: 14px !important;
    }

    .ariya-post-layout-v1 > .ariya-post-featured-v1 > img {
        max-height: 260px !important;
    }
}
</style>
@endpush
