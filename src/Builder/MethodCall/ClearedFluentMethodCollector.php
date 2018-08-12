<?php declare(strict_types=1);

namespace Rector\Builder\MethodCall;

final class ClearedFluentMethodCollector
{
    /**
     * @var string[][]
     */
    private $methodsByClass = [];

    public function addClassAndMethod(string $className, string $methodName): void
    {
        $this->methodsByClass[$className][] = $methodName;
    }

    /**
     * @return string[][]
     */
    public function getMethodsByClass(): array
    {
        return $this->methodsByClass;
    }
}
