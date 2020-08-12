<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareParentCommand extends Command
{
    public function getContainer(): ContainerInterface
    {
    }
}
