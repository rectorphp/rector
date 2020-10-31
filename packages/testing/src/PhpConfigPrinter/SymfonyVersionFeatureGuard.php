<?php

declare(strict_types=1);

namespace Rector\Testing\PhpConfigPrinter;

use Migrify\PhpConfigPrinter\Contract\SymfonyVersionFeatureGuardInterface;

final class SymfonyVersionFeatureGuard implements SymfonyVersionFeatureGuardInterface
{
    public function isAtLeastSymfonyVersion(float $symfonyVersion): bool
    {
        return true;
    }
}
