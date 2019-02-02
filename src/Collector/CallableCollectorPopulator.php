<?php declare(strict_types=1);

namespace Rector\Collector;

use Closure;
use Rector\Exception\DependencyInjection\CallableCollectorException;
use ReflectionFunction;

final class CallableCollectorPopulator
{
    /**
     * @param string[]|callable[]|Closure[] $callables
     * @return callable[]
     */
    public function populate(array $callables): array
    {
        $populatedCallables = [];

        foreach ($callables as $key => $callable) {
            // 1. convert instant assign to callable
            if (! is_callable($callable)) {
                $populatedCallables[$key] = function () use ($callable) {
                    return $callable;
                };
                continue;
            }

            $parameterType = $this->resolveCallableParameterType($callable);
            $this->ensureCallableParameterIsUnique($populatedCallables, $parameterType);

            $populatedCallables[$parameterType] = $callable;
        }

        return $populatedCallables;
    }

    private function resolveCallableParameterType(callable $callable): string
    {
        $reflectionFunction = new ReflectionFunction($callable);
        $this->ensureCallableHasExactlyOneParameter($reflectionFunction);

        $reflectionParameter = $reflectionFunction->getParameters()[0];

        $type = (string) $reflectionParameter->getType();
        $this->ensureParameterHasType($type);

        return $type;
    }

    /**
     * @param callable[] $callables
     */
    private function ensureCallableParameterIsUnique(array $callables, string $parameterType): void
    {
        if (! isset($callables[$parameterType])) {
            return;
        }

        throw new CallableCollectorException(sprintf(
            'There can be only one callable for "%s" parameter type',
            $parameterType
        ));
    }

    private function ensureCallableHasExactlyOneParameter(ReflectionFunction $reflectionFunction): void
    {
        if ($reflectionFunction->getNumberOfParameters() !== 1) {
            throw new CallableCollectorException(sprintf(
                'Collector callable has to have exactly 1 parameter. %d found.',
                $reflectionFunction->getNumberOfParameters()
            ));
        }
    }

    private function ensureParameterHasType(string $type): void
    {
        if ($type) {
            return;
        }

        throw new CallableCollectorException('Collector callable parmaeter has to have type declaration.');
    }
}
