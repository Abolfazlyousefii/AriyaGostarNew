<style id="ariya-bulk-ui-v41">
/* AriyaGostar group-discount V4: scoped to this page only. */
.bd4-page {max-width:1420px; margin:0 auto; padding-bottom:44px; color:#25314d;}
.bd4-heading {display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:18px; margin:0 0 24px;}
.bd4-heading h3 {font-weight:800; margin:0 0 8px; color:#16284d;}
.bd4-heading p {color:#687992; margin:0;}
.bd4-summary {background:#eef3ff; color:#3554a0; padding:12px 20px; border-radius:14px; font-size:20px; font-weight:800;}
.bd4-summary span {font-size:12px; font-weight:400;}
.bd4-card {background:#fff; padding:24px; border:1px solid #e7ecf4; border-radius:16px; margin-bottom:20px; box-shadow:0 4px 24px rgba(30,53,91,.04);}
.bd4-section-head {display:flex; align-items:center; gap:12px; margin-bottom:22px;}
.bd4-section-head h4 {color:#1b3060; font-weight:750; margin:0 0 3px;}
.bd4-section-head p {color:#7a859a; margin:0; font-size:13px;}
.bd4-step {background:#ebf0ff; color:#4968bc; width:42px; height:42px; border-radius:13px; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:19px; flex-shrink:0;}
.bd4-card .form-group label {font-weight:650; display:block; margin-bottom:7px; color:#42516c;}
.bd4-card .form-control {border:1px solid #dbe3ef; border-radius:9px; min-height:42px; color:#293957;}
.bd4-card .form-control:focus {border-color:#738ee1; box-shadow:0 0 0 3px #648bff20;}
.bd4-hint {background:#f6f8fe; color:#627490; padding:11px 14px; border-radius:8px; font-size:12px;}
.bd4-scope-switch {display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; margin-bottom:18px;}
.bd4-scope-switch label {padding:17px 11px; border:1px solid #dce5f2; border-radius:12px; cursor:pointer; display:flex; gap:10px; align-items:center; margin:0; background:#fbfcff; font-weight:700;}
.bd4-scope-switch label:has(input:checked) {border-color:#657ed2; background:#eef3ff; color:#264694;}
.bd4-scope-switch input, .bd4-node input, .bd4-product-row input, .bd4-exclude-row input {accent-color:#5275ce; width:17px; height:17px; flex-shrink:0;}
.bd4-all-note {padding:22px; border-radius:12px; background:#f4f8ff; color:#4d648c;}
.bd4-browser {border:1px solid #e1e7f1; border-radius:14px; overflow:hidden; margin-bottom:18px; background:#fff;}
.bd4-browser-head {background:#f5f7fc; padding:15px 17px; display:flex; align-items:center; gap:14px; justify-content:space-between;}
.bd4-browser-head strong {color:#25395f; font-size:15px;}
.bd4-browser-head span {color:#8190a7; font-size:12px;}
.bd4-toolbar {display:flex; flex-wrap:wrap; align-items:center; gap:8px; padding:12px 14px; border-bottom:1px solid #e8edf5;}
.bd4-toolbar input {flex:1 1 220px;}
.bd4-toolbar button {white-space:nowrap;}
.bd4-tree {height:380px; max-height:65vh; overflow:auto; padding:13px 16px; background:#fff;}
.bd4-node {margin:4px 0; border-radius:9px;}
.bd4-node[open]>.bd4-node-head {background:#f7f9fe;}
.bd4-node-head {display:flex; gap:10px; align-items:center; padding:10px 9px; border-radius:8px; cursor:pointer; list-style:none; color:#35496c; font-weight:650;}
.bd4-node-head::-webkit-details-marker {display:none;}
.bd4-node-head:hover {background:#f0f5ff;}
.bd4-node-toggle {color:#8d9ab3; display:inline-block; transform:rotate(180deg); transition:transform .15s; font-size:14px;}
.bd4-node[open]>.bd4-node-head>.bd4-node-toggle {transform:rotate(90deg);}
.bd4-node-label {display:inline-flex; gap:10px; align-items:center; cursor:pointer; flex:1; margin:0;}
.bd4-node-title {flex:1;}
.bd4-count {border-radius:50px; background:#e7eefb; color:#596f9b; font-size:11px; min-width:32px; text-align:center; padding:4px 7px; margin-right:auto;}
.bd4-node-children {margin-right:23px; padding-right:16px; border-right:1px dashed #dbe4f0;}
.bd4-product-row {display:flex; align-items:center; gap:9px; padding:8px 10px; margin-bottom:3px; border-radius:7px; cursor:pointer; font-size:12px;}
.bd4-product-row:hover,.bd4-exclude-row:hover {background:#f3f7ff;}
.bd4-product-row span,.bd4-exclude-row span {flex:1;}
.bd4-product-row small,.bd4-exclude-row small {color:#97a3b7; direction:ltr;}
.bd4-choose-branch {border:1px solid #cad9f2; background:#fff; border-radius:7px; padding:4px 9px; color:#5072b0; cursor:pointer; font-size:11px;}
.bd4-choose-branch:hover {background:#dce8ff;}
.bd4-selected-note {border-top:1px solid #e9eef7; background:#f9fbfe; display:flex; align-items:center; justify-content:space-between; padding:10px 14px; font-weight:600; color:#4966a0;}
.bd4-exclusion {border:1px solid #e1e7f1; border-radius:14px; padding:15px; margin-bottom:15px;}
.bd4-exclusion strong {color:#294269;}
.bd4-exclusion p {font-size:12px; color:#8491a7; margin:5px 0 12px;}
.bd4-exclude-list {overflow:auto; height:170px; max-height:45vh; border:1px solid #e4eaf3; border-radius:10px; padding:5px; margin-top:10px;}
.bd4-exclude-row {display:flex; gap:9px; align-items:center; padding:8px; border-radius:7px; cursor:pointer; margin:0; font-size:12px;}
.bd4-replace {font-size:13px; display:flex; gap:8px; align-items:center;}
.bd4-replace small {color:#7c8ca5;}
.bd4-action-card {background:#fafcff;}
.bd4-action-card>p {color:#677890; font-size:13px;}
.bd4-preview {margin-top:22px; border-top:1px solid #dce5f3; padding-top:20px;}
.bd4-preview>h5 {font-weight:750;}
.bd4-inline-date {display:flex; flex-wrap:wrap; gap:6px; min-width:530px; align-items:center;}
.bd4-inline-date>select {width:145px;}
.bd4-inline-date>input {width:180px;}
.bd4-button-row {display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-top:15px;}
.bd4-button-row form {margin:0;}
.bd4-jalali {text-align:center; font-variant-numeric:tabular-nums;}
.bd4-page .datepicker-container {z-index:12000!important;}
@media(max-width:760px) {.bd4-card{padding:15px}.bd4-scope-switch{grid-template-columns:1fr}.bd4-browser-head{display:block}.bd4-browser-head span{display:block;margin-top:3px}.bd4-tree{height:330px}.bd4-toolbar .btn{flex:1}.bd4-heading h3{font-size:22px}.bd4-inline-date{min-width:420px}}


/* V4.1: resilient layout even if the previous external assets fail to load. */
.bd4-page {max-width:1280px!important;margin:0 auto!important;padding:16px 14px 44px!important;direction:rtl;font-size:14px;}
.bd4-heading{padding:4px 2px 8px;margin-bottom:14px;align-items:flex-start;}
.bd4-heading h3{font-size:22px;line-height:1.4;margin:0 0 5px;}
.bd4-heading p{max-width:780px;font-size:13px;line-height:1.85;}
.bd4-summary {font-size:22px;padding:10px 16px;box-shadow:inset 0 0 0 1px #dbe6fd;}
.bd4-summary span{display:block;line-height:1.5;}
.bd4-page .bd4-card{display:block!important;background:#fff!important;border:1px solid #e0e8f4!important;padding:20px 22px!important;border-radius:15px!important;margin-bottom:16px!important;box-shadow:0 6px 20px rgba(27,42,79,.045)!important;min-width:0;}
.bd4-page .bd4-section-head{margin:0 0 17px!important;align-items:center;}
.bd4-page .bd4-section-head h4{font-size:17px;line-height:1.4;margin:0 0 3px;}
.bd4-page .bd4-section-head p{font-size:12px;line-height:1.6;}
.bd4-page .bd4-step{height:35px;width:35px;border-radius:10px;font-size:16px;}
.bd4-page .form-group{margin-bottom:13px;}
.bd4-page .form-group label{font-size:12px;font-weight:700;line-height:1.6;}
.bd4-page input.form-control,.bd4-page select.form-control{height:43px;min-height:43px;width:100%;max-width:100%;font-size:13px;box-sizing:border-box;}
.bd4-page .row {margin-left:-7px;margin-right:-7px;}
.bd4-page .row>[class*="col-"] {padding-left:7px;padding-right:7px;}
.bd4-page .bd4-scope-switch{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:14px;}
.bd4-page .bd4-scope-switch label{display:flex;gap:9px;min-height:52px;align-items:center;font-size:13px;padding:12px 14px;background:#fafbfe;}
.bd4-page .bd4-scope-switch input {width:17px;height:17px;flex:0 0 17px;}
/* Progressive disclosure without JS. Prevent rendering 1300+ products as a long page. */
#ariya-bulk-discount-form [data-for-scope]{display:none;}
#ariya-bulk-discount-form:has(input[name="scope"][value="all"]:checked) [data-for-scope="all"],
#ariya-bulk-discount-form:has(input[name="scope"][value="categories"]:checked) [data-for-scope="categories"],
#ariya-bulk-discount-form:has(input[name="scope"][value="products"]:checked) [data-for-scope="products"]{display:block;}
.bd4-page .bd4-browser{overflow:hidden!important;border-radius:11px;border:1px solid #dce6f2;max-width:100%;margin-bottom:12px;}
.bd4-page .bd4-browser-head{padding:11px 14px;gap:7px;flex-wrap:wrap;}
.bd4-page .bd4-browser-head strong{font-size:14px;}
.bd4-page .bd4-toolbar{display:flex;gap:7px;padding:9px 11px;align-items:center;}
.bd4-page .bd4-toolbar input{flex:1 1 230px;min-width:150px;width:auto;}
.bd4-page .bd4-toolbar button{border-radius:8px;padding:7px 10px;font-size:11px;white-space:nowrap;}
.bd4-page .bd4-tree{height:330px!important;max-height:330px!important;overflow-y:auto!important;overflow-x:hidden!important;padding:8px 12px!important;contain:layout style;scrollbar-gutter:stable;}
.bd4-page .bd4-node{margin:2px 0;}
.bd4-page .bd4-node-head{min-height:35px;padding:6px 9px;gap:8px;flex-wrap:wrap;font-size:13px;}
.bd4-page .bd4-node-label{margin:0;display:flex;align-items:center;gap:8px;min-width:0;}
.bd4-page .bd4-node-label span,.bd4-page .bd4-node-title{min-width:0;overflow-wrap:anywhere;}
.bd4-page .bd4-node-children{margin-right:14px;padding-right:12px;margin-left:0;}
.bd4-page .bd4-product-row,.bd4-page .bd4-exclude-row{font-size:12px;min-height:32px;line-height:1.4;margin-bottom:2px;}
.bd4-page .bd4-exclusion{padding:12px 14px;margin-top:14px;margin-bottom:14px;background:#fcfdff;}
.bd4-page .bd4-exclusion>div:first-child p{margin:4px 0 9px;line-height:1.7;}
.bd4-page .bd4-exclude-list{height:150px!important;max-height:150px!important;overflow:auto!important;scrollbar-gutter:stable;}
.bd4-page .bd4-hint{line-height:1.8;margin:0;}
.bd4-page .bd4-replace{margin:13px 0 0;align-items:center;line-height:1.7;}
.bd4-page .bd4-action-card{background:#f8fbff!important;border-color:#cddcf8!important;}
.bd4-page .bd4-all-note{padding:14px 16px;font-size:13px;line-height:1.8;}
.bd4-page .bd4-selected-note{min-height:35px;padding:7px 12px;}
.bd4-page .bd4-preview{overflow-x:auto;}
.bd4-page .table-responsive{max-width:100%;}
.bd4-page .bd4-inline-date{min-width:600px;}
@media(max-width:960px){.bd4-page{padding:10px 4px 32px!important}.bd4-page .bd4-card{padding:15px!important}.bd4-page .bd4-toolbar button{font-size:10px}.bd4-page .bd4-tree{height:300px!important;max-height:300px!important;}}
@media(max-width:620px){.bd4-page .bd4-scope-switch{grid-template-columns:1fr;gap:7px}.bd4-heading{gap:8px}.bd4-page .bd4-browser-head{display:block}.bd4-page .bd4-tree{height:280px!important;max-height:280px!important}.bd4-page .bd4-toolbar input{flex-basis:100%;}.bd4-page .bd4-section-head h4{font-size:15px}.bd4-page .bd4-card{padding:12px!important}}

</style>
