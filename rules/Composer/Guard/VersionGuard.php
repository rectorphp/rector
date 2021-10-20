<?php

declare (strict_types=1);
namespace Rector\Composer\Guard;

use RectorPrefix20211020\Composer\Semver\VersionParser;
use Rector\Composer\Contract\VersionAwareInterface;
final class VersionGuard
{
    /**
     * @var \Composer\Semver\VersionParser
     */
    private $versionParser;
    public function __construct(\RectorPrefix20211020\Composer\Semver\VersionParser $versionParser)
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
