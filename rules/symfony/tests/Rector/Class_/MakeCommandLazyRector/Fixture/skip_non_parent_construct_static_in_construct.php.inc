<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;

final class SkipNonParentConstructStaticInConstruct extends Command
{
    public function __construct()
    {
        Foo::init();
    }
}

?>
