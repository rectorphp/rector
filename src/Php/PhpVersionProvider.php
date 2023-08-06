<?php

declare (strict_types=1);
namespace Rector\Core\Php;

use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\Core\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver;
use Rector\Core\Util\StringUtils;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use ReflectionClass;
/**
 * @see \Rector\Core\Tests\Php\PhpVersionProviderTest
 */
final class PhpVersionProvider
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver
     */
    private $projectComposerJsonPhpVersionResolver;
    /**
     * @var string
     * @see https://regex101.com/r/qBMnbl/1
     */
    private const VALID_PHP_VERSION_REGEX = '#^\\d{5,6}$#';
    public function __construct(ProjectComposerJsonPhpVersionResolver $projectComposerJsonPhpVersionResolver)
    {
        $this->projectComposerJsonPhpVersionResolver = $projectComposerJsonPhpVersionResolver;
    }
    /**
     * @return PhpVersion::*
     */
    public function provide() : int
    {
        $phpVersionFeatures = null;
        if (SimpleParameterProvider::hasParameter(Option::PHP_VERSION_FEATURES)) {
            $phpVersionFeatures = SimpleParameterProvider::provideIntParameter(Option::PHP_VERSION_FEATURES);
            $this->validatePhpVersionFeaturesParameter($phpVersionFeatures);
        }
        if ($phpVersionFeatures > 0) {
            return $phpVersionFeatures;
        }
        // for tests
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            // so we don't have to keep with up with newest version
            return PhpVersion::PHP_10;
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
        $phpVersionReflectionClass = new ReflectionClass(PhpVersion::class);
        // @todo check
        if (\in_array($phpVersionFeatures, $phpVersionReflectionClass->getConstants(), \true)) {
            return;
        }
        if (!\is_int($phpVersionFeatures)) {
            $this->throwInvalidTypeException($phpVersionFeatures);
        }
        if (StringUtils::isMatch((string) $phpVersionFeatures, self::VALID_PHP_VERSION_REGEX) && $phpVersionFeatures >= PhpVersion::PHP_53 - 1) {
            return;
        }
        $this->throwInvalidTypeException($phpVersionFeatures);
    }
    /**
     * @return never
     * @param mixed $phpVersionFeatures
     */
    private function throwInvalidTypeException($phpVersionFeatures)
    {
        $errorMessage = \sprintf('Parameter "%s::%s" must be int, "%s" given.%sUse constant from "%s" to provide it, e.g. "%s::%s"', Option::class, 'PHP_VERSION_FEATURES', (string) $phpVersionFeatures, \PHP_EOL, PhpVersion::class, PhpVersion::class, 'PHP_80');
        throw new InvalidConfigurationException($errorMessage);
    }
}
