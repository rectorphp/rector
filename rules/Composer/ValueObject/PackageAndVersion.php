<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject;

use Rector\Composer\Contract\VersionAwareInterface;

final class PackageAndVersion implements VersionAwareInterface
{
    public function __construct(
        private string $packageName,
        private string $version
    ) {
    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
