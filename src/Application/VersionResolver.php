<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use DateTime;
use Rector\Core\Exception\VersionException;
use RectorPrefix20220501\Symfony\Component\Console\Command\Command;
use RectorPrefix20220501\Symfony\Component\Process\Process;
/**
 * Inspired by https://github.com/composer/composer/blob/master/src/Composer/Composer.php
 * See https://github.com/composer/composer/blob/6587715d0f8cae0cd39073b3bc5f018d0e6b84fe/src/Composer/Compiler.php#L208
 */
final class VersionResolver
{
    /**
     * @var string
     */
    public const PACKAGE_VERSION = '0.12.23';
    /**
     * @var string
     */
    public const RELEASE_DATE = '2022-05-01 14:53:41';
    /**
     * @var string
     */
    private const GIT = 'git';
    public static function resolvePackageVersion() : string
    {
        $pointsAtProcess = new \RectorPrefix20220501\Symfony\Component\Process\Process([self::GIT, 'tag', '--points-at'], __DIR__);
        if ($pointsAtProcess->run() !== \RectorPrefix20220501\Symfony\Component\Console\Command\Command::SUCCESS) {
            throw new \Rector\Core\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        $tag = \trim($pointsAtProcess->getOutput());
        if ($tag !== '' && $tag !== '0') {
            return $tag;
        }
        $process = new \RectorPrefix20220501\Symfony\Component\Process\Process([self::GIT, 'log', '--pretty="%H"', '-n1', 'HEAD'], __DIR__);
        if ($process->run() !== \RectorPrefix20220501\Symfony\Component\Console\Command\Command::SUCCESS) {
            throw new \Rector\Core\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        $version = \trim($process->getOutput());
        return \trim($version, '"');
    }
    public static function resolverReleaseDateTime() : \DateTime
    {
        $process = new \RectorPrefix20220501\Symfony\Component\Process\Process([self::GIT, 'log', '-n1', '--pretty=%ci', 'HEAD'], __DIR__);
        if ($process->run() !== \RectorPrefix20220501\Symfony\Component\Console\Command\Command::SUCCESS) {
            throw new \Rector\Core\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        return new \DateTime(\trim($process->getOutput()));
    }
}
