<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\UseAlias\TestOther as TestOtherAlias;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\UseAlias as TestFoo;

final class AliasUsed
{
    /**
    * @TestFoo\TestSmth
    */
    private string $foo;

    /**
    * @TestFoo\TestOther
    */
    private string $bar;

    /**
    * @TestOtherAlias
    */
    private string $baz;

}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\UseAlias\TestOther as TestOtherAlias;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\UseAlias as TestFoo;

final class AliasUsed
{
    #[TestFoo\TestSmth]
    private string $foo;

    #[TestFoo\TestOther]
    private string $bar;

    #[TestOtherAlias]
    private string $baz;

}

?>
