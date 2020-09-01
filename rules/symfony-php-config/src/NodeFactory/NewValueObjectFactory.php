<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\NodeFactory;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use Rector\Core\PhpParser\Node\NodeFactory;
use ReflectionClass;

final class NewValueObjectFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function create(object $valueObject): New_
    {
        $valueObjectClass = get_class($valueObject);

        $propertyValues = $this->resolvePropertyValuesFromValueObject($valueObjectClass, $valueObject);
        $args = $this->nodeFactory->createArgs($propertyValues);

        return new New_(new Name($valueObjectClass), $args);
    }

    /**
     * @return mixed[]
     */
    private function resolvePropertyValuesFromValueObject(string $valueObjectClass, object $valueObject): array
    {
        $reflectionClass = new ReflectionClass($valueObjectClass);
        $propertyValues = [];
        foreach ($reflectionClass->getProperties() as $propertyReflection) {
            $propertyReflection->setAccessible(true);

            $propertyValues[] = $propertyReflection->getValue($valueObject);
        }
        return $propertyValues;
    }
}
