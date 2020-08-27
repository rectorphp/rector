<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Collector;

final class FormerVariablesByMethodCollector
{
    /**
     * @var array<string, array<string, string>>
     */
    private $variablesByMethod = [];

    public function addMethodVariable(string $method, string $variable, string $type): void
    {
        $this->variablesByMethod[$method][$variable] = $type;
    }

    public function reset(): void
    {
        $this->variablesByMethod = [];
    }

    public function getTypeByVariableByMethod(string $methodName, string $variableName): ?string
    {
        return $this->variablesByMethod[$methodName][$variableName] ?? null;
    }
}
