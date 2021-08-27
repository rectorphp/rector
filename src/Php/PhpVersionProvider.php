<?php

declare (strict_types=1);
namespace Rector\Core\Php;

use Rector\Core\Configuration\Option;
use Rector\Core\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use RectorPrefix20210827\Symplify\PackageBuilder\Parameter\ParameterProvider;
/**
 * @see \Rector\Core\Tests\Php\PhpVersionProviderTest
 */
final class PhpVersionProvider
{
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var \Rector\Core\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver
     */
    private $projectComposerJsonPhpVersionResolver;
    public function __construct(\RectorPrefix20210827\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\Core\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver $projectComposerJsonPhpVersionResolver)
    {
        $this->parameterProvider = $parameterProvider;
        $this->projectComposerJsonPhpVersionResolver = $projectComposerJsonPhpVersionResolver;
    }
    public function provide() : int
    {
        $phpVersionFeatures = $this->parameterProvider->provideIntParameter(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES);
        if ($phpVersionFeatures > 0) {
            return $phpVersionFeatures;
        }
        // for tests
        if (\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
            // so we don't have to up
            return 100000;
        }
        $projectComposerJson = \getcwd() . '/composer.json';
        if (\file_exists($projectComposerJson)) {
            $phpVersion = $this->projectComposerJsonPhpVersionResolver->resolve($projectComposerJson);
            if ($phpVersion !== null) {
                return $phpVersion;
            }
        }
        return \PHP_VERSION_ID;
    }
    public function isAtLeastPhpVersion(int $phpVersion) : bool
    {
        return $phpVersion <= $this->provide();
    }
}
