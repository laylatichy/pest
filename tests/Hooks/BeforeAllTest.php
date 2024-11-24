<?php

$_SERVER['beforeAllCalls'] = 0;

pest()->beforeAll(function () {
    $_SERVER['beforeAllCalls']++;

    expect($_SERVER['beforeAllCalls'])->toBe(1);
});

beforeAll(function () {
    $_SERVER['beforeAllCalls']++;

    expect($_SERVER['beforeAllCalls'])->toBe(2);
});

test('beforeAll is called', function () {
    expect($_SERVER['beforeAllCalls'])->toBe(2);
});
