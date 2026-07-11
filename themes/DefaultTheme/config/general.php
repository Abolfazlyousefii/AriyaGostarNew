<?php

$default_images_path = 'src/resources/assets/img/default/';

return [
    'theme_name' => 'DefaultTheme',

    'sliderGroups' => [
        [
            'group'   => 'main_sliders',
            'name'    => 'اسلایدر اصلی',
            'width'   => 1780,
            'height'  => 890,
            'count'   => 2,
            'size'    => '890 * 1780',
            'images'  => [
                $default_images_path . 'slider1.jpg',
                $default_images_path . 'slider2.jpg',
            ],
        ],
        [
            'group'   => 'fullscreen_slider',
            'name'    => 'اسلایدر تمام صفحه',
            'width'   => 1600,
            'height'  => 600,
            'count'   => 2,
            'size'    => '600 * 1800',
            'images'  => [
                $default_images_path . 'fullscreen.jpg',
                $default_images_path . 'fullscreen2.jpg',
            ],
        ],
        [
            'group'   => 'mobile_sliders',
            'name'    => 'اسلایدر حالت موبایل',
            'width'   => 658,
            'height'  => 436,
            'count'   => 2,
            'size'    => '436 * 658',
            'images'  => [
                $default_images_path . 'slider3.jpg',
                $default_images_path . 'slider4.jpg',
            ],
        ],
        [
            'group'  => 'coworker_sliders',
            'name'   => 'اسلایدر لوگو همکاران',
            'width'  => 100,
            'height' => 100,
            'count'  => 5,
            'size'   => '100 * 100',
            'images'  => [
                $default_images_path . 'slider5.png',
                $default_images_path . 'slider6.png',
                $default_images_path . 'slider7.png',
                $default_images_path . 'slider8.png',
                $default_images_path . 'slider9.png',
            ],
        ],
        [
            'group'  => 'sevices_sliders',
            'name'   => 'اسلایدر خدمات',
            'width'  => 60,
            'height' => 60,
            'count'  => 3,
            'size'   => '60 * 60',
            'images'  => [
                $default_images_path . 'slider10.svg',
                $default_images_path . 'slider11.svg',
                $default_images_path . 'slider12.svg',
            ],
            'titles' => [
                'پشتیبانی قوی',
                'کالای اصل',
                'ارسال سریع کالا',
            ]
        ],
    ],

    'bannerGroups' => [
        [
            'group'   => 'index_middle_banners',
            'name'    => 'بنر دوتایی',
            'width'   => 820,
            'height'  => 300,
            'count'   => 2,
            'size'    => '300 * 820',
            'images'  => [
                $default_images_path . 'banner3.jpg',
                $default_images_path . 'banner4.jpg',
            ],

        ],
        [
            'group'   => 'index_slider_banners',
            'name'    => 'بنر کنار اسلایدر اصلی',
            'width'   => 856,
            'height'  => 428,
            'count'   => 2,
            'size'    => '428 * 856',
            'images'  => [
                $default_images_path . 'banner1.jpg',
                $default_images_path . 'banner2.jpg',
            ],
        ],
        [
            'name'  => 'گیف بالای هدر',
            'group' => 'top_header_gif',
            'size'  => '1920 * 80',
        ],
    ],

    'imageSizes' => [
        'productCategoryImage'  => '500 * 500',
        'CategoryImage'         => '500 * 500',
        'postImage'             => '300 * 400',
        'productGalleryImage'   => '720 * 1280',
        'productImage'          => '600 * 600',
        'logo'                  => '36 * 128',
        'icon'                  => '32 * 32',
    ],

    'linkGroups' => [
        [
            'name'  => 'گروه اول',
            'key'   => 1,
        ],
        [
            'name'  => 'گروه دوم',
            'key'   => 2,
        ],
        [
            'name'  => 'گروه سوم',
            'key'   => 3,
        ],
    ],

    'errors' => [
        '404' => 'front::errors.404'
    ],

    'pages' => [
        'login'            => 'front::auth.login',
        'register'         => 'front::auth.register',
        'forgot-password'  => 'front::auth.forgot-password',
        'login-with-code'  => 'front::auth.login-with-code',
        'one-time-login'   => 'front::auth.one-time-login',
    ],

    'routes' => [
        'verify'                 => 'front.verify.showVerify',
        'change-password'        => 'front.user.force-change-password',
        'change-password-routes' => ['front.user.force-change-password', 'front.user.force-update-password'],
    ],

    'exceptVerifyCsrfToken' => [
        'orders/payment/callback/*',
        'orders/shipping-cost/callback/*',
        'wallet/payment/callback/*',
    ],

    'asset_path' => 'themes/defaultTheme/',
    'theme_path' => 'themes/DefaultTheme/',
    'mainfest_path' => 'themes/defaultTheme',
    'demo' => [
        'image' => 'demo/preview.jpg',
        'name'  => 'قالب پیش فرض',
        'description' => 'قالب پیش فرض اسکریپت فروشگاهی لاراول شاپ'
    ],

    'socials' => [
        [
            'name' => 'تلگرام',
            'key' => 'social_telegram',
            'icon' => 'fa fa-telegram',
        ],
        [
            'name' => 'اینستاگرام',
            'key' => 'social_instagram',
            'icon' => 'feather icon-instagram',
        ],
        [
            'name' => 'واتساپ',
            'key' => 'social_whatsapp',
            'icon' => 'fa fa-whatsapp',
        ],
    ],

    'settings' => [
        'fields' => [
            [
                'title'      => 'رنگ بندی قالب',
                'key'        => 'dt_theme_color',
                'input-type' => 'select',
                'class'      => 'col-md-4 col-6',
                'options'    => [
                    [
                        'value' => 'default',
                        'title' => 'پیش فرض'
                    ],
                    [
                        'value' => 'amber-color',
                        'title' => 'کهربایی'
                    ],
                    [
                        'value' => 'blue-color',
                        'title' => 'آبی'
                    ],
                    [
                        'value' => 'blue-grey-color',
                        'title' => 'آبی خاکستری'
                    ],
                    [
                        'value' => 'brown-color',
                        'title' => 'قهوه ای'
                    ],
                    [
                        'value' => 'cyan-color',
                        'title' => 'فیروزه ای'
                    ],
                    [
                        'value' => 'green-color',
                        'title' => 'سبز'
                    ],
                    [
                        'value' => 'indigo-color',
                        'title' => 'نیلی'
                    ],
                    [
                        'value' => 'lime-color',
                        'title' => 'لیمویی'
                    ],
                    [
                        'value' => 'orange-color',
                        'title' => 'نارنجی'
                    ],
                    [
                        'value' => 'purple-color',
                        'title' => 'بنفش'
                    ],
                    [
                        'value' => 'red-color',
                        'title' => 'قرمز'
                    ],
                    [
                        'value' => 'teal-color',
                        'title' => 'سبز پر رنگ'
                    ],
                ],
                'attributes' => 'required'
            ],
            [
                'title'      => 'نمایش نمودار قیمت محصول',
                'key'        => 'dt_show_price_change_chart',
                'input-type' => 'select',
                'class'      => 'col-md-3 col-6',
                'options'    => [
                    [
                        'value' => 'yes',
                        'title' => 'بله'
                    ],
                    [
                        'value' => 'no',
                        'title' => 'خیر'
                    ]
                ],
                'attributes' => 'required'
            ],
            [
                'title'      => 'نمایش اشتراک گذاری محصول',
                'key'        => 'show_product_share_links',
                'input-type' => 'select',
                'class'      => 'col-md-3 col-6',
                'options'    => [
                    [
                        'value' => '1',
                        'title' => 'بله'
                    ],
                    [
                        'value' => '0',
                        'title' => 'خیر'
                    ]
                ],
                'attributes' => 'required'
            ],
            [
                'title'      => 'متن توضیحات نظرات محصول',
                'key'        => 'dt_product_reviews_description',
                'input-type' => 'inline-editor',
                'class'      => 'col-md-7',
            ],
            [
                'input-type' => 'linebreak',
                'html' => '<hr>'
            ],
            [
                'title'      => 'نوع پاپ آپ صفحه اصلی',
                'key'        => 'dt_index_popup_type',
                'input-type' => 'select',
                'class'      => 'col-md-3 col-6',
                'options'    => [
                    [
                        'value' => 'none',
                        'title' => 'هیچکدام'
                    ],
                    [
                        'value' => 'image',
                        'title' => 'تصویر'
                    ],
                    [
                        'value' => 'text',
                        'title' => 'متن'
                    ]
                ],
                'attributes' => 'required'
            ],
            [
                'title'      => 'تصویر پاپ آپ',
                'key'        => 'dt_index_popup_image',
                'input-type' => 'file',
                'class'      => 'col-md-3 col-6',
            ],
            [
                'title'      => 'تصویر پاپ آپ مخصوص موبایل',
                'key'        => 'dt_index_popup_image_mobile',
                'input-type' => 'file',
                'class'      => 'col-md-3 col-6',
            ],
            [
                'title'      => 'لینک تصویر',
                'key'        => 'dt_index_popup_link',
                'input-type' => 'input',
                'type'       => 'text',
                'class'      => 'col-md-3 col-6',
            ],
            [
                'title'      => 'متن پاپ آپ',
                'key'        => 'dt_index_popup_text',
                'input-type' => 'inline-editor',
                'class'      => 'col-md-7',
            ],
            [
    'input-type' => 'linebreak',
    'html' => '<hr><h4>تنظیمات فوتر آریاجانبی</h4>'
],
[
    'title'      => 'لوگوی فوتر',
    'key'        => 'ariya_footer_logo',
    'input-type' => 'file',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'شعار کوتاه کنار لوگوی فوتر',
    'key'        => 'ariya_footer_brandline',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-8 col-12',
],
[
    'title'      => 'عنوان بخش معرفی فوتر',
    'key'        => 'ariya_footer_about_title',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'متن معرفی فوتر',
    'key'        => 'ariya_footer_about_text',
    'input-type' => 'inline-editor',
    'class'      => 'col-md-8 col-12',
],
[
    'input-type' => 'linebreak',
    'html'       => '<hr><h5>مزیت‌های بالای فوتر</h5>',
],
[
    'title'      => 'عنوان مزیت اول',
    'key'        => 'ariya_footer_feature_1_title',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-3 col-12',
],
[
    'title'      => 'توضیح مزیت اول',
    'key'        => 'ariya_footer_feature_1_text',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-3 col-12',
],
[
    'title'      => 'عنوان مزیت دوم',
    'key'        => 'ariya_footer_feature_2_title',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-3 col-12',
],
[
    'title'      => 'توضیح مزیت دوم',
    'key'        => 'ariya_footer_feature_2_text',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-3 col-12',
],
[
    'title'      => 'عنوان مزیت سوم',
    'key'        => 'ariya_footer_feature_3_title',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-3 col-12',
],
[
    'title'      => 'توضیح مزیت سوم',
    'key'        => 'ariya_footer_feature_3_text',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-3 col-12',
],
[
    'title'      => 'عنوان مزیت چهارم',
    'key'        => 'ariya_footer_feature_4_title',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-3 col-12',
],
[
    'title'      => 'توضیح مزیت چهارم',
    'key'        => 'ariya_footer_feature_4_text',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-3 col-12',
],
[
    'input-type' => 'linebreak',
    'html'       => '<hr><h5>گروه‌های لینک فوتر</h5>',
],
[
    'title'      => 'عنوان گروه لینک اول',
    'key'        => 'ariya_footer_group_1_title',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'کلید گروه لینک اول',
    'key'        => 'ariya_footer_group_1_key',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-2 col-12',
],
[
    'title'      => 'عنوان گروه لینک دوم',
    'key'        => 'ariya_footer_group_2_title',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'کلید گروه لینک دوم',
    'key'        => 'ariya_footer_group_2_key',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-2 col-12',
],
[
    'title'      => 'عنوان گروه لینک سوم',
    'key'        => 'ariya_footer_group_3_title',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'کلید گروه لینک سوم',
    'key'        => 'ariya_footer_group_3_key',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-2 col-12',
],
[
    'title'      => 'عنوان دسترسی سریع قدیمی',
    'key'        => 'ariya_footer_quick_title',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-3 col-12',
],
[
    'title'      => 'کلید گروه لینک قدیمی',
    'key'        => 'ariya_footer_quick_group_key',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-3 col-12',
],
[
    'title'      => 'عنوان اطلاعات تماس',
    'key'        => 'ariya_footer_contact_title',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'آدرس شعبه',
    'key'        => 'ariya_footer_address',
    'input-type' => 'inline-editor',
    'class'      => 'col-md-8 col-12',
],
[
    'title'      => 'لینک آدرس روی نقشه',
    'key'        => 'ariya_footer_address_link',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'ایمیل',
    'key'        => 'ariya_footer_email',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'واتساپ و تلفن',
    'key'        => 'ariya_footer_whatsapp_phone',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'لینک واتساپ یا تلفن',
    'key'        => 'ariya_footer_whatsapp_link',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'شماره گارانتی',
    'key'        => 'ariya_footer_warranty_phone',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'لینک شماره گارانتی',
    'key'        => 'ariya_footer_warranty_link',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'کد نماد اعتماد فوتر',
    'key'        => 'ariya_footer_enamad_code',
    'input-type' => 'inline-editor',
    'class'      => 'col-md-8 col-12',
],
[
    'title'      => 'کد ساماندهی فوتر',
    'key'        => 'ariya_footer_samandehi_code',
    'input-type' => 'inline-editor',
    'class'      => 'col-md-8 col-12',
],
[
    'title'      => 'لینک اینستاگرام فوتر',
    'key'        => 'ariya_footer_instagram_link',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'لینک تلگرام فوتر',
    'key'        => 'ariya_footer_telegram_link',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
[
    'title'      => 'لینک واتساپ فوتر',
    'key'        => 'ariya_footer_whatsapp_social_link',
    'input-type' => 'input',
    'type'       => 'text',
    'class'      => 'col-md-4 col-12',
],
        ],
        'rules' => [
            'dt_show_price_change_chart' => 'required|in:yes,no'
        ]
    ],

    'links' => [
        public_path('themes/defaultTheme') => base_path('themes/DefaultTheme/src/resources/assets'),
    ],

    'home-widgets' => require __DIR__ . '/../config/widgets.php',

    'cache-forget' => [
        'categories' => [
            'front.productcats',
            'front.index.categories'
        ],
        'products' => [],
        'posts'    => [],
        'sliders'  => [],
        'banners'  => []
    ]
];
