<?php

namespace Rector\Tests\Symfony\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;

final class SkipNonParentConstructStaticInConstruct extends Command
{
    public function __construct()
    {
        Foo::init();
    }
}

?>
