<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use DateTime;
use Rector\Core\Exception\VersionException;
/**
 * Inspired by https://github.com/composer/composer/blob/master/src/Composer/Composer.php
 * See https://github.com/composer/composer/blob/6587715d0f8cae0cd39073b3bc5f018d0e6b84fe/src/Composer/Compiler.php#L208
 *
 * @see \Rector\Core\Tests\Application\VersionResolverTest
 */
final class VersionResolver
{
    /**
     * @api
     * @var string
     */
    public const PACKAGE_VERSION = 'f8221e3e21c487f2d18707f7b979ef6607204826';
    /**
     * @api
     * @var string
     */
    public const RELEASE_DATE = '2022-07-03 22:37:10';
    /**
     * @var int
     */
    private const SUCCESS_CODE = 0;
    public static function resolvePackageVersion() : string
    {
        // resolve current tag
        \exec('git tag --points-at', $tagExecOutput, $tagExecResultCode);
        if ($tagExecResultCode !== self::SUCCESS_CODE) {
            throw new VersionException('Ensure to run compile from composer git repository clone and that git binary is available.');
        }
        if ($tagExecOutput !== []) {
            $tag = $tagExecOutput[0];
            if ($tag !== '') {
                return $tag;
            }
        }
        \exec('git log --pretty="%H" -n1 HEAD', $commitHashExecOutput, $commitHashResultCode);
        if ($commitHashResultCode !== 0) {
            throw new VersionException('Ensure to run compile from composer git repository clone and that git binary is available.');
        }
        $version = \trim((string) $commitHashExecOutput[0]);
        return \trim($version, '"');
    }
    public static function resolverReleaseDateTime() : DateTime
    {
        \exec('git log -n1 --pretty=%ci HEAD', $output, $resultCode);
        if ($resultCode !== self::SUCCESS_CODE) {
            throw new VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        return new DateTime(\trim((string) $output[0]));
    }
}
