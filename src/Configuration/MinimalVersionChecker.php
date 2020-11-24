<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Rector\Core\Configuration\MinimalVersionChecker\ComposerJsonParser;
use Rector\Core\Exception\Application\PhpVersionException;
use Rector\Core\Util\StaticPhpVersion;

/**
 * @see \Rector\Core\Tests\Configuration\MinimalVersionCheckerTest
 */
final class MinimalVersionChecker
{
    /**
     * @var string
     */
    private $installedPhpVersion;

    /**
     * @var ComposerJsonParser
     */
    private $composerJsonParser;

    public function __construct(string $installedPhpVersion, ComposerJsonParser $composerJsonParser)
    {
        $this->installedPhpVersion = $installedPhpVersion;
        $this->composerJsonParser = $composerJsonParser;
    }

    public function check(): void
    {
        $minimumPhpVersion = $this->composerJsonParser->getPhpVersion();

        $intInstalledPhpVersion = StaticPhpVersion::getIntVersion($this->installedPhpVersion);
        $intMinimumPhpVersion = StaticPhpVersion::getIntVersion($minimumPhpVersion);

        // Check minimum required PHP version
        if ($intInstalledPhpVersion < $intMinimumPhpVersion) {
            throw new PhpVersionException(sprintf(
                'PHP version %s or higher is required, but you currently have %s installed.',
                $minimumPhpVersion,
                $this->installedPhpVersion
            ));
        }
    }
}
