<?php

namespace Rector\Php\Tests\Rector\ConstFetch\ConstantReplacerRector\Fixture;

final class Spaghetti
{
    public function run()
    {
        if (OLD_CONSTANT) { ?><some html><?php }
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ConstFetch\ConstantReplacerRector\Fixture;

final class Spaghetti
{
    public function run()
    {
        if (NEW_CONSTANT) { ?><some html><?php }
    }
}

?>
