<?php

declare (strict_types=1);
namespace Rector\Symfony\Contract\Bridge\Symfony\Routing;

use Rector\Symfony\ValueObject\SymfonyRouteMetadata;
interface SymfonyRoutesProviderInterface
{
    /**
     * @return SymfonyRouteMetadata[]
     */
    public function provide() : array;
}
