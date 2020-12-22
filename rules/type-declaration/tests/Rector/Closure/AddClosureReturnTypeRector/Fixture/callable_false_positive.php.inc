<?php

namespace Rector\TypeDeclaration\Tests\Rector\Closure\AddClosureReturnTypeRector\Fixture;

class CallableFalsePositive
{
    /**
     * @param callable[]|string[] $callables
     * @return callable[]
     */
    public function populate(array $callables): array
    {
        $populatedCallables = [];

        foreach ($callables as $key => $callable) {
            // 1. convert instant assign to callable
            if (!is_callable($callable)) {
                $populatedCallables[$key] = function () use ($callable) {
                    return $callable;
                };
                continue;
            }
        }

        return $populatedCallables;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Closure\AddClosureReturnTypeRector\Fixture;

class CallableFalsePositive
{
    /**
     * @param callable[]|string[] $callables
     * @return callable[]
     */
    public function populate(array $callables): array
    {
        $populatedCallables = [];

        foreach ($callables as $key => $callable) {
            // 1. convert instant assign to callable
            if (!is_callable($callable)) {
                $populatedCallables[$key] = function () use ($callable): string {
                    return $callable;
                };
                continue;
            }
        }

        return $populatedCallables;
    }
}

?>
