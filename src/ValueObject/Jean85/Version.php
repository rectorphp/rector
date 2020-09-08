<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Jean85;

use Nette\Utils\Strings;

/**
 * Temporary local fork, until PHP 8.0 gets merged and tagged
 * https://github.com/Jean85/pretty-package-versions/pull/25
 */
final class Version
{
    /**
     * @var int
     */
    private const SHORT_COMMIT_LENGTH = 7;

    /**
     * @var string
     */
    private $shortVersion;

    /**
     * @var string
     */
    private $commitHash;

    /**
     * @var bool
     */
    private $versionIsTagged = false;

    public function __construct(string $version)
    {
        $splittedVersion = explode('@', $version);
        $this->shortVersion = $splittedVersion[0];

        $this->commitHash = $splittedVersion[1];
        $this->versionIsTagged = (bool) Strings::match($this->shortVersion, '#[^v\d\.]#');
    }

    public function getPrettyVersion(): string
    {
        if ($this->versionIsTagged) {
            return $this->shortVersion;
        }

        return $this->getVersionWithShortCommit();
    }

    private function getVersionWithShortCommit(): string
    {
        return $this->shortVersion . '@' . $this->getShortCommitHash();
    }

    private function getShortCommitHash(): string
    {
        return Strings::substring($this->commitHash, 0, self::SHORT_COMMIT_LENGTH);
    }
}
