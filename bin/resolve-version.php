<?php

declare (strict_types=1);
namespace RectorPrefix20220529;

// resolve git version stored in released rector
// tag for tagged one, hash for dev
/**
 * Inspired by https://github.com/composer/composer/blob/master/src/Composer/Composer.php
 * See https://github.com/composer/composer/blob/6587715d0f8cae0cd39073b3bc5f018d0e6b84fe/src/Composer/Compiler.php#L208
 *
 * @see \Rector\Core\Tests\Application\VersionResolverTest
 */
final class VersionResolver
{
    /**
     * @var int
     */
    private const SUCCESS_CODE = 0;
    public function resolve() : string
    {
        // resolve current tag
        \exec('git tag --points-at', $tagExecOutput, $tagExecResultCode);
        if ($tagExecResultCode !== self::SUCCESS_CODE) {
            die('Ensure to run compile from composer git repository clone and that git binary is available.');
        }
        // debug output
        \var_dump($tagExecOutput);
        if ($tagExecOutput !== []) {
            $tag = $tagExecOutput[0];
            if ($tag !== '') {
                return $tag;
            }
        }
        \exec('git log --pretty="%H" -n1 HEAD', $commitHashExecOutput, $commitHashResultCode);
        if ($commitHashResultCode !== 0) {
            die('Ensure to run compile from composer git repository clone and that git binary is available.');
        }
        // debug output
        \var_dump($commitHashExecOutput);
        $version = \trim($commitHashExecOutput[0]);
        return \trim($version, '"');
    }
}
// resolve git version stored in released rector
// tag for tagged one, hash for dev
/**
 * Inspired by https://github.com/composer/composer/blob/master/src/Composer/Composer.php
 * See https://github.com/composer/composer/blob/6587715d0f8cae0cd39073b3bc5f018d0e6b84fe/src/Composer/Compiler.php#L208
 *
 * @see \Rector\Core\Tests\Application\VersionResolverTest
 */
\class_alias('VersionResolver', 'VersionResolver', \false);
$versionResolver = new \RectorPrefix20220529\VersionResolver();
echo $versionResolver->resolve() . \PHP_EOL;
