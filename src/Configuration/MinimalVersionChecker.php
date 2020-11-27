<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Rector\Core\Configuration\MinimalVersionChecker\ComposerJsonParser;
use Rector\Core\Exception\Application\PhpVersionException;
use Rector\Core\Util\PhpVersionFactory;

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

    /**
     * @var PhpVersionFactory
     */
    private $phpVersionFactory;

    public function __construct(string $installedPhpVersion, ComposerJsonParser $composerJsonParser)
    {
        $this->installedPhpVersion = $installedPhpVersion;
        $this->composerJsonParser = $composerJsonParser;
        $this->phpVersionFactory = new PhpVersionFactory();
    }

    public function check(): void
    {
        $minimumPhpVersion = $this->composerJsonParser->getPhpVersion();

        $intInstalledPhpVersion = $this->phpVersionFactory->createIntVersion($this->installedPhpVersion);
        $intMinimumPhpVersion = $this->phpVersionFactory->createIntVersion($minimumPhpVersion);

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
