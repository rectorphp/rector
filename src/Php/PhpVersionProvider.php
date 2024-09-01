<?php

declare (strict_types=1);
namespace Rector\Php;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php\PhpVersionResolver\ComposerJsonPhpVersionResolver;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Rector\Util\StringUtils;
use Rector\ValueObject\PhpVersion;
use ReflectionClass;
/**
 * @see \Rector\Tests\Php\PhpVersionProviderTest
 */
final class PhpVersionProvider
{
    /**
     * @var string
     * @see https://regex101.com/r/qBMnbl/1
     */
    private const VALID_PHP_VERSION_REGEX = '#^\\d{5,6}$#';
    /**
     * @var int|null
     */
    private $phpVersionFeatures = null;
    /**
     * @return PhpVersion::*
     */
    public function provide() : int
    {
        if (SimpleParameterProvider::hasParameter(Option::PHP_VERSION_FEATURES)) {
            $this->phpVersionFeatures = SimpleParameterProvider::provideIntParameter(Option::PHP_VERSION_FEATURES);
            $this->validatePhpVersionFeaturesParameter($this->phpVersionFeatures);
        }
        if ($this->phpVersionFeatures > 0) {
            return $this->phpVersionFeatures;
        }
        // for tests
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            // so we don't have to keep with up with newest version
            return PhpVersion::PHP_10;
        }
        $projectComposerJson = \getcwd() . '/composer.json';
        if (\file_exists($projectComposerJson)) {
            $phpVersion = ComposerJsonPhpVersionResolver::resolve($projectComposerJson);
            if ($phpVersion !== null) {
                return $this->phpVersionFeatures = $phpVersion;
            }
        }
        // fallback to current PHP runtime version
        return $this->phpVersionFeatures = \PHP_VERSION_ID;
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
