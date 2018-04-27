<?php declare(strict_types=1);

namespace Rector\Bridge\Symfony;

use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Symplify\PackageBuilder\Exception\Yaml\InvalidParametersValueException;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class DefaultServiceTypeForNameProvider implements ServiceTypeForNameProviderInterface
{
    /**
     * @var string
     */
    private const KERNEL_CLASS_PARAMETER = 'kernel_class';

    /**
     * @var string[]
     */
    private $nameToTypeMap = [
        'some_service' => 'stdClass',
    ];

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }

    public function provideTypeForName(string $name): ?string
    {
        $kernelClass = $this->parameterProvider->provideParameter(self::KERNEL_CLASS_PARAMETER);
        $this->ensureKernelClassIsValid($kernelClass);

        // make this default, register and require kernel_class paramter, see:
        // https://github.com/rectorphp/rector/issues/428

        return $this->nameToTypeMap[$name] ?? null;
    }

    private function ensureKernelClassIsValid(?string $kernelClass): void
    {
        if ($kernelClass === null) {
            throw new InvalidConfigurationException(sprintf(
                'Make sure "%s" pareters is set in rector.yml in "parameters:" section',
                self::KERNEL_CLASS_PARAMETER
            ));
        }

        if (! class_exists($kernelClass)) {
            throw new InvalidParametersValueException(sprintf(
                'Kernel class "%s" provided in "parameters > %s" is not autoloadable. ' .
                'Make sure composer.json of your application is valid and rector is loading "vendor/autoload.php" of your application.',
                $kernelClass,
                self::KERNEL_CLASS_PARAMETER
            ));
        }
    }
}
