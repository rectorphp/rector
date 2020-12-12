<?php

namespace Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector\Fixture;

use Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector\Source\Apple\Red;
use Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector\Source\Banana\Green;

class IgnoreNamespace
{
    public function __construct(
        Red $red,
        Green $green
    ) {
    }
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector\Fixture;

use Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector\Source\Apple\Red;
use Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector\Source\Banana\Green;

class IgnoreNamespace
{
    public function __construct(
        Green $green,
        Red $red
    ) {
    }
}

?>
