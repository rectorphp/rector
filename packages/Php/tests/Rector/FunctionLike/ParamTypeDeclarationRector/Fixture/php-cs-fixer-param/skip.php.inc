<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\SkipMixed;

/** @param $bar mixed */ function my_foo($bar) {}

/** @param $bar Foo|Bar */ function my_foo2($bar) {}

/** @param $bar resource */ function my_foo3($bar) {}

class Foo {
    /** @param static $foo */ function my_foo($foo) {}
}

class FooFoo { /** @param Bar $bar */ public function foo($tab) { } }

/** @param int $foo */ function foo(int $foo) {}

/** @param string $foo */ function foo2(int $foo) {}

/** @param $bar null */ function my_foo4($bar) {}

/** @param $foo \Foo\\Bar */ function my_foo5($foo) {}

/** @param $foo string */ function my_foo6($foo) {}

/**
 * @param string $bar
 */
function my_foo7() {}
function my_foo8($bar) {}

/** @param \9 */ function my_foo9($bar) {}

/** @param $foo string */ function my_foo10($foo) {}

/** @param */ function my_foo11($bar) {}

function my_foo12() {}

/**
 * @param mixed $bar
 */
function my_foo13($bar) {}
