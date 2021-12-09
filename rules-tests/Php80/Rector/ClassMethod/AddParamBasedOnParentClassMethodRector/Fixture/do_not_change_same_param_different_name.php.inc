<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Fixture;

use Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Source\MultiParams;

class DoNotChangeSameParamDifferentName extends MultiParams{
    public function execute($x)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Fixture;

use Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Source\MultiParams;

class DoNotChangeSameParamDifferentName extends MultiParams{
    public function execute($x, $bar)
    {
    }
}

?>
