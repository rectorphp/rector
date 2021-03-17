<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector\Fixture;

class FinalPrivate
{
    final private function run($array)
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector\Fixture;

class FinalPrivate
{
    private function run($array)
    {
        return $this;
    }
}

?>
