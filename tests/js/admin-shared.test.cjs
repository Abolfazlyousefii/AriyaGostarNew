const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');

const source = fs.readFileSync(path.join(__dirname, '../../public/back/assets/js/scripts.js'), 'utf8');

function loadSharedScript(withSelect2) {
    const selections = [];
    const chain = { on() { return this; }, attr() { return this; }, trigger() { return this; }, change() { return this; } };
    if (withSelect2) chain.select2 = function () { return this; };
    const $ = selector => { selections.push(selector); return chain; };
    $.fn = withSelect2 ? { select2() {} } : {};
    $.ajaxSetup = () => {};
    vm.runInNewContext(source, { $, document: {}, window: {} });
    return selections;
}

test('shared admin script loads on authentication pages without Select2', () => {
    const selections = loadSharedScript(false);
    assert.ok(selections.includes('.ajax-form'), 'handlers after optional widgets must still be registered');
});

test('shared admin script still initializes Select2 when available', () => {
    assert.ok(loadSharedScript(true).includes('select.select2'));
});
