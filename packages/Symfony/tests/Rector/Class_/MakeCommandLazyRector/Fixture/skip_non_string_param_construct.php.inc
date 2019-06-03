<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;

final class SkipNonStringParamConstruct extends Command
{
    private $another;

    public function __construct($someService, $another)
    {
        parent::__construct($someService);
        $this->another = $another;
    }
}
