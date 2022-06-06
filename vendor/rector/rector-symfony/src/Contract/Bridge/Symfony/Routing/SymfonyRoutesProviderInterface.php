<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Contract\Bridge\Symfony\Routing;

use RectorPrefix20220606\Rector\Symfony\ValueObject\SymfonyRouteMetadata;
interface SymfonyRoutesProviderInterface
{
    /**
     * @return SymfonyRouteMetadata[]
     */
    public function provide() : array;
}
