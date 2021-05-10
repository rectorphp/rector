<?php

declare(strict_types=1);

namespace Rector\Composer\Guard;

use Composer\Semver\VersionParser;
use Rector\Composer\Contract\VersionAwareInterface;

final class VersionGuard
{
    public function __construct(
        private VersionParser $versionParser
    ) {
    }

    /**
     * @param VersionAwareInterface[] $versionAwares
     */
    public function validate(array $versionAwares): void
    {
        foreach ($versionAwares as $versionAware) {
            $this->versionParser->parseConstraints($versionAware->getVersion());
        }
    }
}
