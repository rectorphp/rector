<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use DateTime;
use Rector\Core\Exception\VersionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

/**
 * Inspired by https://github.com/composer/composer/blob/master/src/Composer/Composer.php
 * See https://github.com/composer/composer/blob/6587715d0f8cae0cd39073b3bc5f018d0e6b84fe/src/Composer/Compiler.php#L208
 *
 * @see \Rector\Core\Tests\Application\VersionResolverTest
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

    /**
     * @var string
     */
    private const GIT = 'git';

    public static function resolvePackageVersion(): string
    {
        // resolve current tag
        exec('git tag --points-at', $tagExecOutput, $tagExecResultCode);

        if ($tagExecResultCode !== Command::SUCCESS) {
            throw new VersionException(
                'Ensure to run compile from composer git repository clone and that git binary is available.'
            );
        }

        if ($tagExecOutput !== []) {
            $tag = $tagExecOutput[0];
            if ($tag !== '') {
                return $tag;
            }
        }

        exec('git log --pretty="%H" -n1 HEAD', $commitHashExecOutput, $commitHashResultCode);

        if ($commitHashResultCode !== Command::SUCCESS) {
            throw new VersionException(
                'Ensure to run compile from composer git repository clone and that git binary is available.'
            );
        }

        $version = trim($commitHashExecOutput[0]);
        return trim($version, '"');
    }

    public static function resolverReleaseDateTime(): DateTime
    {
        $process = new Process([self::GIT, 'log', '-n1', '--pretty=%ci', 'HEAD'], __DIR__);
        if ($process->run() !== Command::SUCCESS) {
            throw new VersionException(
                'You must ensure to run compile from composer git repository clone and that git binary is available.'
            );
        }

        return new DateTime(trim($process->getOutput()));
    }
}
