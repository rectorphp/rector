<?php

declare(strict_types=1);

namespace Rector\Composer\Guard;

use Composer\Semver\VersionParser;
use Rector\Composer\Contract\VersionAwareInterface;

final class VersionGuard
{
    /**
     * @var VersionParser
     */
    private $versionParser;

    public function __construct(VersionParser $versionParser)
    {
        $this->versionParser = $versionParser;
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
