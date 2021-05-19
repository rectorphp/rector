<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use DateTime;
use Rector\Core\Exception\VersionException;
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Console\ShellCode;

/**
 * Inspired by https://github.com/composer/composer/blob/master/src/Composer/Composer.php
 * See https://github.com/composer/composer/blob/6587715d0f8cae0cd39073b3bc5f018d0e6b84fe/src/Composer/Compiler.php#L208
 */
final class VersionResolver
{
    /**
     * @var string
     */
    public const PACKAGE_VERSION = '@package_version@';

    /**
     * @var string
     */
    public const RELEASE_DATE = '@release_date@';

    public static function resolvePackageVersion(): string
    {
        $process = new Process(['git', 'log', '--pretty="%H"', '-n1', 'HEAD'], __DIR__);
        if ($process->run() !== ShellCode::SUCCESS) {
            throw new VersionException(
                'You must ensure to run compile from composer git repository clone and that git binary is available.'
            );
        }
        return trim($process->getOutput());
    }

    public static function resolverReleaseDateTime(): DateTime
    {
        $process = new Process(['git', 'log', '-n1', '--pretty=%ci', 'HEAD'], __DIR__);
        if ($process->run() !== ShellCode::SUCCESS) {
            throw new VersionException(
                'You must ensure to run compile from composer git repository clone and that git binary is available.'
            );
        }

        return new DateTime(trim($process->getOutput()));
    }
}
