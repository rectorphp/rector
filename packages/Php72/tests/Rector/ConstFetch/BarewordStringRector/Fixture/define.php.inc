<?php

namespace Rector\Php72\Tests\Rector\ConstFetch\BarewordStringRector\Fixture;

function run_define()
{
    define(NAME, 'Tom');
    define('AGE', 1000);
}
