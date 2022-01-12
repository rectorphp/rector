<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector\Fixture;

abstract class FinalPrivate
{
    final private function __construct()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector\Fixture;

abstract class FinalPrivate
{
    final private function __construct()
    {
    }
}

?>
