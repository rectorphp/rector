<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Composer\Guard;

use RectorPrefix20220606\Composer\Semver\VersionParser;
use RectorPrefix20220606\Rector\Composer\Contract\VersionAwareInterface;
final class VersionGuard
{
    /**
     * @readonly
     * @var \Composer\Semver\VersionParser
     */
    private $versionParser;
    public function __construct(VersionParser $versionParser)
    {
        $this->versionParser = $versionParser;
    }
    /**
     * @param VersionAwareInterface[] $versionAwares
     */
    public function validate(array $versionAwares) : void
    {
        foreach ($versionAwares as $versionAware) {
            $this->versionParser->parseConstraints($versionAware->getVersion());
        }
    }
}
