<?php

namespace Foo;

class Foo
{
    public function __construct()
    {
        $bar = 'baz';
        print $bar{2};
    }
}
