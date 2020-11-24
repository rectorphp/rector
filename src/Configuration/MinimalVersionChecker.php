<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Nette\Utils\Strings;
use Rector\Core\Configuration\MinimalVersionChecker\ComposerJsonParser;
use Rector\Core\Exception\Application\PhpVersionException;
use Rector\Core\Util\PhpVersion;

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

        // Check minimum required PHP version
        if (PhpVersion::getIntVersion($this->installedPhpVersion) < PhpVersion::getIntVersion($minimumPhpVersion)) {
            throw new PhpVersionException(sprintf(
                'PHP version %s or higher is required, but you currently have %s installed.',
                $minimumPhpVersion,
                $this->installedPhpVersion
            ));
        }
    }
}
