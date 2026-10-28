const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const {test} = require('node:test');
const source = fs.readFileSync(path.join(__dirname, '../../themes/DefaultTheme/src/resources/assets/js/pages/products/stock-preflight.js'), 'utf8');

function screen(enabled = true) {
    let quantity = 1, scheduled, listener, text = '';
    const calls = [], document = {};
    const notice = {length: enabled ? 1 : 0, data: () => '/cart-stock/10', removeClass() {return this;}, addClass() {return this;}, text(value) {text = value; return this;}};
    const button = {length: 1, data: () => 7};
    const $ = selector => {
        if (selector === document) return {on(events, target, callback) {if (events === 'input change') listener = callback;}};
        if (selector === '#stock-preflight-feedback') return notice;
        if (selector === '[data-model-card].is-selected') return {each() {}};
        if (selector === '.add-to-cart[data-price_id]') return {first: () => button};
        if (selector === '#cart-quantity') return {val: () => quantity};
        throw Error('Unexpected selector');
    };
    $.ajax = options => {calls.push(options); return {abort() {options.error({}, 'abort');}};};
    vm.runInNewContext(source, {jQuery: $, document, setTimeout(fn) {scheduled = fn;}, clearTimeout() {}});
    return {calls, text: () => text, change(value) {quantity = value; listener(); scheduled();}};
}

test('pre-cart stock checks use read-only GET requests', () => {
    const ui = screen();
    assert.equal(ui.calls[0].type, 'GET');
    assert.equal(ui.calls[0].data.items[0].quantity, 1);
});

test('a late response for an older quantity cannot overwrite the current warning', () => {
    const ui = screen();
    ui.change(8);
    ui.calls[1].success({status: 'error', errors: ['فقط ۳ عدد موجود است']});
    ui.calls[0].success({status: 'success'});
    assert.equal(ui.text(), 'فقط ۳ عدد موجود است');
});

test('pages without an authenticated order form issue no inventory request', () => {
    assert.equal(screen(false).calls.length, 0);
});
