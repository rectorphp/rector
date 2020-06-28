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

        $methodBuilder = new Method('__construct');
        $methodBuilder->makePublic();

        foreach ($properties as $propertyName => $property) {
            /** @var string $propertyName */
            $paramBuilder = new Param($propertyName);

            /** @var Property $property */
            if ($property->type !== null) {
                $paramBuilder->setType($property->type);
            }

            $methodBuilder->addParam($paramBuilder->getNode());

            // add assign
            $assign = $this->createAssign($propertyName);

            $methodBuilder->addStmt($assign);
        }

        return $methodBuilder->getNode();
    }

    private function createAssign(string $propertyName): Assign
    {
        $localPropertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

        return new Assign($localPropertyFetch, new Variable($propertyName));
    }
}
