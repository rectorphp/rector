<?php

namespace Rector\Php80\Tests\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector\Fixture;

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

namespace Rector\Php80\Tests\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector\Fixture;

class FinalPrivate
{
    private function run($array)
    {
        return $this;
    }
}

?>
