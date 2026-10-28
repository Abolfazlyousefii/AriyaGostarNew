const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');

const source = fs.readFileSync(path.join(__dirname, '../../public/back/assets/js/pages/login.js'), 'utf8');

function login(globals = {}, response = 'success') {
    let submit;
    const document = {};
    const window = { location: { href: '' }, ...globals };
    const $ = target => target === document
        ? { ready: callback => callback() }
        : { submit: callback => { submit = callback; }, attr: () => '/login' };
    $.isEmptyObject = value => value == null || Object.keys(Object(value)).length === 0;
    $.ajax = options => options.success(response);
    const context = { document, window, $, FormData: class {}, ...globals };
    vm.runInNewContext(source, context);
    submit.call({}, { preventDefault() {} });
    return window.location.href;
}

test('successful login reaches the admin panel without an inline redirect variable', () => {
    assert.equal(login({ BASE_URL: '/admin' }), '/admin');
});

test('successful login preserves the intended destination', () => {
    assert.equal(login({ redirect_url: '/admin/products', BASE_URL: '/admin' }), '/admin/products');
});

test('empty intended destination falls back to the admin panel', () => {
    assert.equal(login({ redirect_url: '', BASE_URL: '/admin' }), '/admin');
});

test('error response does not redirect', () => {
    assert.equal(login({ BASE_URL: '/admin' }, { error: 'Invalid credentials' }), '');
});
