<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

class SomeClass
{
}

class SkipNotAnonymous
{
    public function run($arg1, $arg2)
    {
        return new SomeClass($arg1, $arg2);
    }
}

?>
