<?php

declare (strict_types=1);
namespace Rector\Core\Php\PhpVersionResolver;

use RectorPrefix20220209\Composer\Semver\VersionParser;
use Rector\Core\Util\PhpVersionFactory;
use RectorPrefix20220209\Symplify\ComposerJsonManipulator\ComposerJsonFactory;
/**
 * @see \Rector\Core\Tests\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver\ProjectComposerJsonPhpVersionResolverTest
 */
final class ProjectComposerJsonPhpVersionResolver
{
    /**
     * @readonly
     * @var \Symplify\ComposerJsonManipulator\ComposerJsonFactory
     */
    private $composerJsonFactory;
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
    public function __construct(\RectorPrefix20220209\Symplify\ComposerJsonManipulator\ComposerJsonFactory $composerJsonFactory, \RectorPrefix20220209\Composer\Semver\VersionParser $versionParser, \Rector\Core\Util\PhpVersionFactory $phpVersionFactory)
    {
        $this->composerJsonFactory = $composerJsonFactory;
        $this->versionParser = $versionParser;
        $this->phpVersionFactory = $phpVersionFactory;
    }
    public function resolve(string $composerJson) : ?int
    {
        $projectComposerJson = $this->composerJsonFactory->createFromFilePath($composerJson);
        // see https://getcomposer.org/doc/06-config.md#platform
        $platformPhp = $projectComposerJson->getConfig()['platform']['php'] ?? null;
        if ($platformPhp !== null) {
            return $this->phpVersionFactory->createIntVersion($platformPhp);
        }
        $requirePhpVersion = $projectComposerJson->getRequirePhpVersion();
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
