<?php

declare(strict_types=1);

namespace Rector\Core\Tests\RectorCombination\RenameAndFluent\Fixture;

use PhpParser\Node\Expr\New_;
use PHPStan\Reflection\ParameterReflection;
use Rector\Core\PHPStan\Reflection\CallReflectionResolver;

final class CyclicParameterReflection
{
    /**
     * @var CallReflectionResolver
     */
    private $callReflectionResolver;

    public function __construct(CallReflectionResolver $callReflectionResolver)
    {
        $this->callReflectionResolver = $callReflectionResolver;
    }

    private function isParameterReferencedInMethodReflection(New_ $new, int $argumentPosition): bool
    {
        $methodReflection = $this->callReflectionResolver->resolveConstructor($new);
        $parametersAcceptor = $this->callReflectionResolver->resolveParametersAcceptor($methodReflection, $new);

        if ($parametersAcceptor === null) {
            return false;
        }

        /** @var ParameterReflection $parameter */
        foreach ($parametersAcceptor->getParameters() as $parameterPosition => $parameter) {
            if ($parameterPosition !== $argumentPosition) {
                continue;
            }

            return $parameter->passedByReference()->yes();
        }

        return false;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\RectorCombination\RenameAndFluent\Fixture;

use PhpParser\Node\Expr\New_;
use PHPStan\Reflection\ParameterReflection;
use Rector\Core\PHPStan\Reflection\CallReflectionResolver;

final class CyclicParameterReflection
{
    /**
     * @var CallReflectionResolver
     */
    private $callReflectionResolver;

    public function __construct(CallReflectionResolver $callReflectionResolver)
    {
        $this->callReflectionResolver = $callReflectionResolver;
    }

    private function isParameterReferencedInMethodReflection(New_ $new, int $argumentPosition): bool
    {
        $methodReflection = $this->callReflectionResolver->resolveConstructor($new);
        $parametersAcceptor = $this->callReflectionResolver->resolveParametersAcceptor($methodReflection, $new);

        if ($parametersAcceptor === null) {
            return false;
        }

        /** @var ParameterReflection $parameterReflection */
        foreach ($parametersAcceptor->getParameters() as $parameterPosition => $parameterReflection) {
            if ($parameterPosition !== $argumentPosition) {
                continue;
            }

            return $parameterReflection->passedByReference()->yes();
        }

        return false;
    }
}

?>
