<?php

declare (strict_types=1);
namespace Rector\Symfony\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
/**
 * All the Symfony 7.x sets are now part of the composer-based set,
 * where every rule is bound to the exact package version it is available from.
 *
 * @see \Rector\Symfony\Set\SetProvider\SymfonySetProvider
 */
final class Symfony7SetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide(): array
    {
        return [];
    }
}
