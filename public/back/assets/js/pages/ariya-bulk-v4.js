/* Ariya bulk discounts V4: progressive enhancement, no external API calls. */
(function () {
'use strict';
function init() {
    var $ = window.jQuery;
    // Persian calendar is the same plugin already bundled with the admin panel.
    if ($ && $.fn && $.fn.persianDatepicker) {
        $('.bd4-jalali').each(function () {
            var $input = $(this);
            if ($input.data('bd4-initialized')) return;
            $input.data('bd4-initialized', true);
            $input.persianDatepicker({
                format: 'YYYY/MM/DD HH:mm',
                initialValue: false,
                autoClose: true,
                timePicker: {enabled: true, meridian: {enabled: false}},
                toolbox: {calendarSwitch: {enabled: false}}
            });
        });
    } else if (document.querySelector('.bd4-jalali')) {
        console.warn('Persian datepicker not loaded; Jalali input can still be entered manually.');
        document.querySelectorAll('.bd4-jalali').forEach(function (el) {el.removeAttribute('inputmode');});
    }
    var form = document.getElementById('ariya-bulk-discount-form');
    if (form) {
        var treeCategory = form.querySelector('[data-bd4-tree="category"]');
        var treeProduct = form.querySelector('[data-bd4-tree="product"]');
        var excludeList = form.querySelector('[data-bd4-tree="exclude"]');
        function qsa(scope, query) {return scope ? Array.prototype.slice.call(scope.querySelectorAll(query)) : [];}
        function uniqueCount(nodes) {var set = {};nodes.forEach(function(el){if(el.checked)set[el.value]=true;});return Object.keys(set).length;}
        function recalc() {
            var values = [
                ['category', uniqueCount(qsa(treeCategory,'[data-bd4-category-choice]'))+' دسته انتخاب شده'],
                ['product', uniqueCount(qsa(treeProduct,'[data-bd4-product-choice]'))+' محصول انتخاب شده'],
                ['exclude', uniqueCount(qsa(excludeList,'input[type="checkbox"]'))+' محصول مستثنا'],
            ];
            values.forEach(function(x){var el=form.querySelector('[data-bd4-count="'+x[0]+'"]');if(el) el.textContent=x[1];});
        }
        function scopeValue() {var checked=form.querySelector('input[name="scope"]:checked');return checked?checked.value:'all';}
        function refreshScope() {qsa(form,'[data-for-scope]').forEach(function(el){el.style.display=el.getAttribute('data-for-scope')===scopeValue()?'':'none';});}
        qsa(form,'input[name="scope"]').forEach(function(x){x.addEventListener('change',refreshScope);});
        // A product may appear in two categories; keep both checkbox instances in sync.
        function syncProduct(id, checked) {qsa(treeProduct,'[data-bd4-product-choice]').forEach(function(x){if(x.value===id)x.checked=checked;});}
        if (treeProduct) treeProduct.addEventListener('change',function(e){if(e.target.matches('[data-bd4-product-choice]')){syncProduct(e.target.value,e.target.checked);recalc();}});
        if (treeCategory) treeCategory.addEventListener('change',recalc);
        if (excludeList) excludeList.addEventListener('change',recalc);
        function openAll(tree, flag) {qsa(tree,'details[data-bd4-category]').forEach(function(node){node.open=flag;});}
        qsa(form,'[data-bd4-expand]').forEach(function(btn){btn.addEventListener('click',function(){openAll(btn.dataset.bd4Expand==='product'?treeProduct:treeCategory,true);});});
        qsa(form,'[data-bd4-collapse]').forEach(function(btn){btn.addEventListener('click',function(){openAll(btn.dataset.bd4Collapse==='product'?treeProduct:treeCategory,false);});});
        function setAll(tree, selector, checked){qsa(tree,selector).forEach(function(el){el.checked=checked;});recalc();}
        var ca=form.querySelector('[data-bd4-all-categories]');if(ca)ca.addEventListener('click',function(){setAll(treeCategory,'[data-bd4-category-choice]',true);});
        var cc=form.querySelector('[data-bd4-clear-categories]');if(cc)cc.addEventListener('click',function(){setAll(treeCategory,'[data-bd4-category-choice]',false);});
        var pc=form.querySelector('[data-bd4-clear-products]');if(pc)pc.addEventListener('click',function(){setAll(treeProduct,'[data-bd4-product-choice]',false);});
        var ec=form.querySelector('[data-bd4-clear-excludes]');if(ec)ec.addEventListener('click',function(){setAll(excludeList,'input[type="checkbox"]',false);});
        var pv=form.querySelector('[data-bd4-select-visible]');if(pv)pv.addEventListener('click',function(){qsa(treeProduct,'[data-bd4-product-row]').forEach(function(row){if(row.style.display!=='none'){var c=row.querySelector('[data-bd4-product-choice]');if(c)syncProduct(c.value,true);}});recalc();});
        if(treeProduct)treeProduct.addEventListener('click',function(e){var btn=e.target.closest('[data-bd4-select-branch]');if(!btn)return;e.preventDefault();e.stopPropagation();var node=btn.closest('[data-bd4-category]');qsa(node,'[data-bd4-product-choice]').forEach(function(x){syncProduct(x.value,true);});recalc();});
        function normalize(s){return (s||'').toLocaleLowerCase().replace(/[۰-۹]/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);}).trim();}
        function searchCategory(value){var needle=normalize(value);if(!treeCategory)return; qsa(treeCategory,'details[data-bd4-category]').forEach(function(node){var own=node.querySelector(':scope > summary');var hit=needle&&normalize(own?own.textContent:'').indexOf(needle)!==-1;if(hit)node.open=true;});if(needle)openAll(treeCategory,true);qsa(treeCategory,'details[data-bd4-category]').reverse().forEach(function(node){var own=node.querySelector(':scope > summary');var match=!needle||normalize(own?own.textContent:'').indexOf(needle)!==-1;var shownChild=qsa(node,':scope > .bd4-node-children > details[data-bd4-category]').some(function(child){return child.style.display!=='none';});node.style.display=match||shownChild?'':'none';});}
        function searchProducts(value){var needle=normalize(value);if(!treeProduct)return;if(needle)openAll(treeProduct,true);qsa(treeProduct,'[data-bd4-product-row]').forEach(function(row){row.style.display=!needle||normalize(row.textContent).indexOf(needle)!==-1?'':'none';});qsa(treeProduct,'details[data-bd4-category]').reverse().forEach(function(node){var own=node.querySelector(':scope > summary');var ownHit=needle&&normalize(own?own.textContent:'').indexOf(needle)!==-1;var shownProducts=qsa(node,':scope > .bd4-node-children > [data-bd4-product-row]').some(function(x){return x.style.display!=='none';});var childHit=qsa(node,':scope > .bd4-node-children > details[data-bd4-category]').some(function(x){return x.style.display!=='none';});node.style.display=!needle||ownHit||shownProducts||childHit?'':'none';if(ownHit)qsa(node,':scope > .bd4-node-children > [data-bd4-product-row]').forEach(function(x){x.style.display='';});});}
        qsa(form,'[data-bd4-search]').forEach(function(input){input.addEventListener('input',function(){var type=input.dataset.bd4Search;if(type==='category')searchCategory(input.value);else if(type==='product')searchProducts(input.value);else qsa(excludeList,'[data-bd4-exclude-row]').forEach(function(row){row.style.display=normalize(row.textContent).indexOf(normalize(input.value))!==-1?'':'none';});});});
        recalc(); refreshScope();
    }
    var add=document.querySelector('[data-bd4-add-form]');
    if(add){var search=add.querySelector('[data-bd4-add-search]'),count=add.querySelector('[data-bd4-add-count]');function c(){count.textContent=add.querySelectorAll('input[type="checkbox"]:checked').length+' محصول انتخاب شده';}add.addEventListener('change',c);if(search)search.addEventListener('input',function(){var v=search.value.toLocaleLowerCase();add.querySelectorAll('[data-bd4-add-row]').forEach(function(row){row.style.display=row.textContent.toLocaleLowerCase().indexOf(v)>=0?'':'none';});});c();}
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
