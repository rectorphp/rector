<?php declare(strict_types=1);

namespace Rector\Node;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;

final class PropertyFetchNodeFactory
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(BuilderFactory $builderFactory)
    {
        $this->builderFactory = $builderFactory;
    }

    /**
     * Creates "$variable->property"
     */
    public function createWithVariableNameAndPropertyName(string $variable, string $propertyName): PropertyFetch
    {
        $variableNode = $this->builderFactory->var($variable);

        return $this->builderFactory->propertyFetch($variableNode, $propertyName);
    }

    /**
     * Creates "$this->propertyName"
     */
    public function createLocalWithPropertyName(string $propertyName): PropertyFetch
    {
        $localVariable = new Variable('this', [
            'name' => $propertyName,
        ]);

        return $this->builderFactory->propertyFetch($localVariable, $propertyName);
    }

    /**
     * Creates "$this->propertyName[]"
     */
    public function createLocalArrayFetchWithPropertyName(string $propertyName): PropertyFetch
    {
        $localVariable = new Variable('this', [
            'name' => $propertyName,
        ]);

        return $this->builderFactory->propertyFetch($localVariable, $propertyName . '[]');
    }
}
