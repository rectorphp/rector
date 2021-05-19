<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use DateTimeInterface;
use Rector\Core\Exception\VersionException;
use RectorPrefix20210519\Symfony\Component\Process\Process;
use RectorPrefix20210519\Symplify\PackageBuilder\Console\ShellCode;
/**
 * Inspired by https://github.com/composer/composer/blob/master/src/Composer/Composer.php
 * See https://github.com/composer/composer/blob/6587715d0f8cae0cd39073b3bc5f018d0e6b84fe/src/Composer/Compiler.php#L208
 */
final class VersionResolver
{
    /**
     * @var string
     */
    public const PACKAGE_VERSION = '"24d0c8efed45b8c86d37ceaf9f4565c1d62f4274"';
    /**
     * @var string
     */
    public const RELEASE_DATE = '2021-05-19 01:15:35';
    public static function resolvePackageVersion() : string
    {
        $process = new \RectorPrefix20210519\Symfony\Component\Process\Process(['git', 'log', '--pretty="%H"', '-n1', 'HEAD'], __DIR__);
        if ($process->run() !== \RectorPrefix20210519\Symplify\PackageBuilder\Console\ShellCode::SUCCESS) {
            throw new \Rector\Core\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        return \trim($process->getOutput());
    }
    public static function resolverReleaseDateTime() : \DateTimeInterface
    {
        $process = new \RectorPrefix20210519\Symfony\Component\Process\Process(['git', 'log', '-n1', '--pretty=%ci', 'HEAD'], __DIR__);
        if ($process->run() !== \RectorPrefix20210519\Symplify\PackageBuilder\Console\ShellCode::SUCCESS) {
            throw new \Rector\Core\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        return new \DateTime(\trim($process->getOutput()));
    }
}
