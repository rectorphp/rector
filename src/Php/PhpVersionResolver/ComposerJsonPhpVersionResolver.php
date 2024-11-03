<?php

declare (strict_types=1);
namespace Rector\Php\PhpVersionResolver;

use RectorPrefix202411\Composer\Semver\VersionParser;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\FileSystem\JsonFileSystem;
use Rector\Util\PhpVersionFactory;
use Rector\ValueObject\PhpVersion;
/**
 * @see \Rector\Tests\Php\PhpVersionResolver\ComposerJsonPhpVersionResolver\ComposerJsonPhpVersionResolverTest
 */
final class ComposerJsonPhpVersionResolver
{
    /**
     * @var array<string, PhpVersion::*|null>
     */
    private static $cachedPhpVersions = [];
    /**
     * @return PhpVersion::*
     */
    public static function resolveFromCwdOrFail() : int
    {
        // use composer.json PHP version
        $projectComposerJsonFilePath = \getcwd() . '/composer.json';
        if (\file_exists($projectComposerJsonFilePath)) {
            $projectPhpVersion = self::resolve($projectComposerJsonFilePath);
            if (\is_int($projectPhpVersion)) {
                return $projectPhpVersion;
            }
        }
        throw new InvalidConfigurationException(\sprintf('We could not find local "composer.json" to determine your PHP version.%sPlease, fill the PHP version set in withPhpSets() manually.', \PHP_EOL));
    }
    /**
     * @return PhpVersion::*|null
     */
    public static function resolve(string $composerJson) : ?int
    {
        if (\array_key_exists($composerJson, self::$cachedPhpVersions)) {
            return self::$cachedPhpVersions[$composerJson];
        }
        $projectComposerJson = JsonFileSystem::readFilePath($composerJson);
        // give this one a priority, as more generic one
        $requirePhpVersion = $projectComposerJson['require']['php'] ?? null;
        if ($requirePhpVersion !== null) {
            self::$cachedPhpVersions[$composerJson] = self::createIntVersionFromComposerVersion($requirePhpVersion);
            return self::$cachedPhpVersions[$composerJson];
        }
        // see https://getcomposer.org/doc/06-config.md#platform
        $platformPhp = $projectComposerJson['config']['platform']['php'] ?? null;
        if ($platformPhp !== null) {
            self::$cachedPhpVersions[$composerJson] = PhpVersionFactory::createIntVersion($platformPhp);
            return self::$cachedPhpVersions[$composerJson];
        }
        return self::$cachedPhpVersions[$composerJson] = null;
    }
    /**
     * @return PhpVersion::*
     */
    private static function createIntVersionFromComposerVersion(string $projectPhpVersion) : int
    {
        $versionParser = new VersionParser();
        $constraint = $versionParser->parseConstraints($projectPhpVersion);
        $lowerBound = $constraint->getLowerBound();
        $lowerBoundVersion = $lowerBound->getVersion();
        return PhpVersionFactory::createIntVersion($lowerBoundVersion);
    }
}
