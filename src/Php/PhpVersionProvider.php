<?php declare(strict_types=1);

namespace Rector\Php;

use Rector\Configuration\Option;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class PhpVersionProvider
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }

    public function provide(): string
    {
        /** @var string|null $phpVersionFeatures */
        $phpVersionFeatures = $this->parameterProvider->provideParameter(Option::PHP_VERSION_FEATURES);
        if ($phpVersionFeatures !== null) {
            return $phpVersionFeatures;
        }

        // for tests
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            return '7.5';
        }

        return PHP_VERSION;
    }

    public function isAtLeast(string $version): bool
    {
        return version_compare($this->provide(), $version) >= 0;
    }
}
