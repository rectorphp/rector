<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Analyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\JMS\SerializerTypeTagValueNode;
use Rector\Core\NodeContainer\NodeCollector\ParsedNodeCollector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ClassAnalyzer
{
    /**
     * @var bool[]
     */
    private $valueObjectStatusByClassName = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ParsedNodeCollector $parsedNodeCollector,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function isValueObjectClass(Class_ $class): bool
    {
        if ($class->isAnonymous()) {
            return false;
        }

        /** @var string $className */
        $className = $this->nodeNameResolver->getName($class);

        if (isset($this->valueObjectStatusByClassName[$className])) {
            return $this->valueObjectStatusByClassName[$className];
        }

        $constructClassMethod = $class->getMethod('__construct');

        if ($constructClassMethod === null) {
            return $this->analyseWithoutConstructor($class, $className);
        }

        // resolve constructor types
        foreach ($constructClassMethod->params as $param) {
            $paramType = $this->nodeTypeResolver->resolve($param);
            if (! $paramType instanceof ObjectType) {
                continue;
            }

            // awesome!
            // is it services or value object?
            $paramTypeClass = $this->parsedNodeCollector->findClass($paramType->getClassName());
            if ($paramTypeClass === null) {
                // not sure :/
                continue;
            }

            if (! $this->isValueObjectClass($paramTypeClass)) {
                return false;
            }
        }

        // if we didn't prove it's not a value object so far → fallback to true
        $this->valueObjectStatusByClassName[$className] = true;

        return true;
    }

    private function hasAllPropertiesWithSerialize(Class_ $class)
    {
        foreach ($class->stmts as $stmt) {
            if (! $stmt instanceof Property) {
                continue;
            }

            /** @var PhpDocInfo $phpDocInfo */
            $phpDocInfo = $stmt->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($phpDocInfo->hasByType(SerializerTypeTagValueNode::class)) {
                continue;
            }

            return false;
        }

        return true;
    }

    private function analyseWithoutConstructor(Class_ $class, ?string $className): bool
    {
        // A. has all properties with serialize?
        if ($this->hasAllPropertiesWithSerialize($class)) {
            $this->valueObjectStatusByClassName[$className] = true;
            return true;
        }

        // probably not a value object
        $this->valueObjectStatusByClassName[$className] = false;
        return false;
    }
}
