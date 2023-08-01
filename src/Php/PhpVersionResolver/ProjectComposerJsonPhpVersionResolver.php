<?php

declare (strict_types=1);
namespace Rector\Core\Php\PhpVersionResolver;

use RectorPrefix202308\Composer\Semver\VersionParser;
use RectorPrefix202308\Nette\Utils\FileSystem;
use RectorPrefix202308\Nette\Utils\Json;
use Rector\Core\Util\PhpVersionFactory;
/**
 * @see \Rector\Core\Tests\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver\ProjectComposerJsonPhpVersionResolverTest
 */
final class ProjectComposerJsonPhpVersionResolver
{
    /**
     * @readonly
     * @var \Composer\Semver\VersionParser
     */
    private $versionParser;
    /**
     * @readonly
     * @var \Rector\Core\Util\PhpVersionFactory
     */
    private $phpVersionFactory;
    public function __construct(VersionParser $versionParser, PhpVersionFactory $phpVersionFactory)
    {
        $this->versionParser = $versionParser;
        $this->phpVersionFactory = $phpVersionFactory;
    }
    public function resolve(string $composerJson) : ?int
    {
        $composerJsonContents = FileSystem::read($composerJson);
        $projectComposerJson = Json::decode($composerJsonContents, Json::FORCE_ARRAY);
        // see https://getcomposer.org/doc/06-config.md#platform
        $platformPhp = $projectComposerJson['config']['platform']['php'] ?? null;
        if ($platformPhp !== null) {
            return $this->phpVersionFactory->createIntVersion($platformPhp);
        }
        $requirePhpVersion = $projectComposerJson['require']['php'] ?? null;
        if ($requirePhpVersion === null) {
            return null;
        }
        return $this->createIntVersionFromComposerVersion($requirePhpVersion);
    }
    private function createIntVersionFromComposerVersion(string $projectPhpVersion) : int
    {
        $constraint = $this->versionParser->parseConstraints($projectPhpVersion);
        $lowerBound = $constraint->getLowerBound();
        $lowerBoundVersion = $lowerBound->getVersion();
        return $this->phpVersionFactory->createIntVersion($lowerBoundVersion);
    }
}
