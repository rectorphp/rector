<?php

declare (strict_types=1);
namespace Rector\Php\PhpVersionResolver;

use RectorPrefix202407\Composer\Semver\VersionParser;
use Rector\FileSystem\JsonFileSystem;
use Rector\Util\PhpVersionFactory;
/**
 * @see \Rector\Tests\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver\ProjectComposerJsonPhpVersionResolverTest
 */
final class ProjectComposerJsonPhpVersionResolver
{
    /**
     * @var array<string, int|null>
     */
    private static $cachedPhpVersions = [];
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
    private static function createIntVersionFromComposerVersion(string $projectPhpVersion) : int
    {
        $versionParser = new VersionParser();
        $constraint = $versionParser->parseConstraints($projectPhpVersion);
        $lowerBound = $constraint->getLowerBound();
        $lowerBoundVersion = $lowerBound->getVersion();
        return PhpVersionFactory::createIntVersion($lowerBoundVersion);
    }
}
