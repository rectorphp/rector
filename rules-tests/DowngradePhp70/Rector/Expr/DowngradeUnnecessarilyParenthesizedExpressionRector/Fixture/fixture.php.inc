<?php

namespace Rector\Tests\DowngradePhp70\Rector\Expr\DowngradeUnnecessarilyParenthesizedExpressionRector\Fixture;

($f)['foo'];
($f)->foo;
($f)->foo();
($f)::$foo;
($f)::foo();
($f)();
(($f))['foo'];
(($f))->foo;
(($f))->foo();
(($f))::$foo;
(($f))::foo();
(($f))();

($f->foo)();
($f::foo)();
(($f->foo))();
(($f::foo))();

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Expr\DowngradeUnnecessarilyParenthesizedExpressionRector\Fixture;

$f['foo'];
$f->foo;
$f->foo();
$f::$foo;
$f::foo();
$f();
$f['foo'];
$f->foo;
$f->foo();
$f::$foo;
$f::foo();
$f();

($f->foo)();
($f::foo)();
($f->foo)();
($f::foo)();

?>
