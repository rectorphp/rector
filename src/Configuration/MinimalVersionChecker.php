<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Rector\Core\Configuration\MinimalVersionChecker\ComposerJsonParser;
use Rector\Core\Exception\Application\PhpVersionException;

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
        if ($this->installedPhpVersion >= preg_replace('#(\d).(\d)#', '${1}0${2}00', $minimumPhpVersion)) {
            throw new PhpVersionException(sprintf(
                'PHP version %s or higher is required, but you currently have %s installed.',
                $minimumPhpVersion,
                $this->installedPhpVersion
            ));
        }
    }
}
