<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector\Fixture;

class SomeClass
{
    public $fn = 'strlen';
}

$cb = ((new SomeClass)->fn)(...);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector\Fixture;

class SomeClass
{
    public $fn = 'strlen';
}

$cb = \Closure::fromCallable((new SomeClass)->fn);

?>
