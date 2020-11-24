<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Nette\Utils\Strings;
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
        $installedPhpVersion = explode('.', $this->installedPhpVersion);

        if (count($installedPhpVersion) > 1) {
            $installedPhpVersion = (string) ($installedPhpVersion[0] * 10000 + (int) $installedPhpVersion[1] * 100 + (int) $installedPhpVersion[2]);
        } else {
            $installedPhpVersion = $this->installedPhpVersion;
        }

        // Check minimum required PHP version
        if ($installedPhpVersion < (int) Strings::replace((string) $minimumPhpVersion, '#^(\d).(\d)$#', '${1}0${2}00')) {
            throw new PhpVersionException(sprintf(
                'PHP version %s or higher is required, but you currently have %s installed.',
                $minimumPhpVersion,
                $this->installedPhpVersion
            ));
        }
    }
}
