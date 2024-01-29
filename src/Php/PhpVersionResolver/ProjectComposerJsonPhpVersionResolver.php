<?php

declare (strict_types=1);
namespace Rector\Php\PhpVersionResolver;

use RectorPrefix202401\Composer\Semver\VersionParser;
use RectorPrefix202401\Nette\Utils\FileSystem;
use RectorPrefix202401\Nette\Utils\Json;
use Rector\Util\PhpVersionFactory;
/**
 * @see \Rector\Tests\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver\ProjectComposerJsonPhpVersionResolverTest
 */
final class ProjectComposerJsonPhpVersionResolver
{
    /**
     * @var array<string, int>
     */
    private static $cachedPhpVersions = [];
    public static function resolve(string $composerJson) : ?int
    {
        if (isset(self::$cachedPhpVersions[$composerJson])) {
            return self::$cachedPhpVersions[$composerJson];
        }
        $composerJsonContents = FileSystem::read($composerJson);
        $projectComposerJson = Json::decode($composerJsonContents, Json::FORCE_ARRAY);
        // see https://getcomposer.org/doc/06-config.md#platform
        $platformPhp = $projectComposerJson['config']['platform']['php'] ?? null;
        if ($platformPhp !== null) {
            self::$cachedPhpVersions[$composerJson] = PhpVersionFactory::createIntVersion($platformPhp);
            return self::$cachedPhpVersions[$composerJson];
        }
        $requirePhpVersion = $projectComposerJson['require']['php'] ?? null;
        if ($requirePhpVersion === null) {
            return null;
        }
        self::$cachedPhpVersions[$composerJson] = self::createIntVersionFromComposerVersion($requirePhpVersion);
        return self::$cachedPhpVersions[$composerJson];
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
