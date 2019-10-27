<?php

declare(strict_types=1);

namespace Rector\Standalone;

use Rector\Console\Style\SymfonyStyleFactory;
use Rector\DependencyInjection\RectorContainerFactory;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class RectorStandaloneRunnerStaticFactory
{
    public static function create(): RectorStandaloneRunner
    {
        $symfonyStyleFactory = new SymfonyStyleFactory(new PrivatesCaller());
        $symfonyStyle = $symfonyStyleFactory->create();

        return new RectorStandaloneRunner(new RectorContainerFactory(), $symfonyStyle);
    }
}
