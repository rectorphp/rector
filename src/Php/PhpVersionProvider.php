<?php

declare (strict_types=1);
namespace Rector\Core\Php;

use Rector\Core\Configuration\Option;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\Core\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver;
use Rector\Core\Util\StringUtils;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use ReflectionClass;
use RectorPrefix20220531\Symplify\PackageBuilder\Parameter\ParameterProvider;
/**
 * @see \Rector\Core\Tests\Php\PhpVersionProviderTest
 */
final class PhpVersionProvider
{
    /**
     * @var string
     * @see https://regex101.com/r/qBMnbl/1
     */
    private const VALID_PHP_VERSION_REGEX = '#^\\d{5,6}$#';
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver
     */
    private $projectComposerJsonPhpVersionResolver;
    public function __construct(\RectorPrefix20220531\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\Core\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver $projectComposerJsonPhpVersionResolver)
    {
        $this->parameterProvider = $parameterProvider;
        $this->projectComposerJsonPhpVersionResolver = $projectComposerJsonPhpVersionResolver;
    }
    /**
     * @return PhpVersion::*
     */
    public function provide() : int
    {
        $phpVersionFeatures = $this->parameterProvider->provideParameter(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES);
        $this->validatePhpVersionFeaturesParameter($phpVersionFeatures);
        if ($phpVersionFeatures > 0) {
            return $phpVersionFeatures;
        }
        // for tests
        if (\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
            // so we don't have to keep with up with newest version
            return \Rector\Core\ValueObject\PhpVersion::PHP_10;
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
    /**
     * @param mixed $phpVersionFeatures
     */
    private function validatePhpVersionFeaturesParameter($phpVersionFeatures) : void
    {
        if ($phpVersionFeatures === null) {
            return;
        }
        // get all constants
        $phpVersionReflectionClass = new \ReflectionClass(\Rector\Core\ValueObject\PhpVersion::class);
        // @todo check
        if (\in_array($phpVersionFeatures, $phpVersionReflectionClass->getConstants(), \true)) {
            return;
        }
        if (!\is_int($phpVersionFeatures)) {
            $this->throwInvalidTypeException($phpVersionFeatures);
        }
        if (\Rector\Core\Util\StringUtils::isMatch((string) $phpVersionFeatures, self::VALID_PHP_VERSION_REGEX) && $phpVersionFeatures >= \Rector\Core\ValueObject\PhpVersion::PHP_53 - 1) {
            return;
        }
        $this->throwInvalidTypeException($phpVersionFeatures);
    }
    /**
     * @param mixed $phpVersionFeatures
     */
    private function throwInvalidTypeException($phpVersionFeatures) : void
    {
        $errorMessage = \sprintf('Parameter "%s::%s" must be int, "%s" given.%sUse constant from "%s" to provide it, e.g. "%s::%s"', \Rector\Core\Configuration\Option::class, 'PHP_VERSION_FEATURES', (string) $phpVersionFeatures, \PHP_EOL, \Rector\Core\ValueObject\PhpVersion::class, \Rector\Core\ValueObject\PhpVersion::class, 'PHP_80');
        throw new \Rector\Core\Exception\Configuration\InvalidConfigurationException($errorMessage);
    }
}
