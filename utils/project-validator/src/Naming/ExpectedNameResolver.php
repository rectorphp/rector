<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Naming;

use Rector\PSR4\Composer\PSR4AutoloadPathsProvider;

final class ExpectedNameResolver
{
    /**
     * @var array<string, string|string[]>
     */
    private $psr4autoloadPaths = [];

    public function __construct(PSR4AutoloadPathsProvider $psr4AutoloadPathsProvider)
    {
        $this->psr4autoloadPaths = $psr4AutoloadPathsProvider->provide();
    }

    public function resolve(string $path, string $relativePath): ?string
    {
        $relativePath = str_replace('/', '\\', dirname($relativePath, PATHINFO_DIRNAME));
        foreach ($this->psr4autoloadPaths as $prefix => $psr4autoloadPath) {
            if (! is_string($psr4autoloadPath)) {
                continue;
            }

            if ($psr4autoloadPath === $path) {
                return $prefix . $relativePath;
            }
        }

        return null;
    }
}
