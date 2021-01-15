<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\Version;

use Composer\Semver\VersionParser;

final class Version
{
    /**
     * @var string
     */
    private $version;

    public function __construct(string $version)
    {
        $versionParser = new VersionParser();
        $versionParser->parseConstraints($version);

        $this->version = $version;
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
