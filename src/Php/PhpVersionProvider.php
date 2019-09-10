<?php declare(strict_types=1);

namespace Rector\Php;

use Rector\Configuration\Option;
use Rector\Testing\PHPUnit\PHPUnitEnvironment;
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
        if (PHPUnitEnvironment::isPHPUnitRun()) {
            return '10.0'; // so we don't have to up
        }

        return PHP_VERSION;
    }

    public function isAtLeast(string $version): bool
    {
        return version_compare($this->provide(), $version) >= 0;
    }
}
