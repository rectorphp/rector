<?php

declare(strict_types=1);

namespace Rector\Decouple\NodeFactory;

use function count;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

final class ConstructorClassMethodFactory
{
    /**
     * @param array<string, Property> $properties
     */
    public function create(array $properties): ?ClassMethod
    {
        if (count($properties) === 0) {
            return null;
        }

        $method = new Method('__construct');
        $method->makePublic();

        foreach ($properties as $propertyName => $property) {
            /** @var string $propertyName */
            $paramBuilder = new Param($propertyName);

            /** @var Property $property */
            if ($property->type !== null) {
                $paramBuilder->setType($property->type);
            }

            $method->addParam($paramBuilder->getNode());

            // add assign
            $assign = $this->createAssign($propertyName);

            $method->addStmt($assign);
        }

        return $method->getNode();
    }

    private function createAssign(string $propertyName): Assign
    {
        $localPropertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

        return new Assign($localPropertyFetch, new Variable($propertyName));
    }
}
