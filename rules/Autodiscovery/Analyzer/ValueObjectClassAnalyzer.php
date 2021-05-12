<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Analyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ValueObjectClassAnalyzer
{
    /**
     * @var array<string, bool>
     */
    private array $valueObjectStatusByClassName = [];

    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private NodeTypeResolver $nodeTypeResolver,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private NodeRepository $nodeRepository,
        private ClassAnalyzer $classAnalyzer
    ) {
    }

    public function isValueObjectClass(Class_ $class): bool
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return false;
        }

        /** @var string $className */
        $className = $this->nodeNameResolver->getName($class);

        if (isset($this->valueObjectStatusByClassName[$className])) {
            return $this->valueObjectStatusByClassName[$className];
        }

        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);

        if (! $constructClassMethod instanceof ClassMethod) {
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
            $paramTypeClass = $this->nodeRepository->findClass($paramType->getClassName());
            if (! $paramTypeClass instanceof Class_) {
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

    private function analyseWithoutConstructor(Class_ $class, string $className): bool
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

    private function hasAllPropertiesWithSerialize(Class_ $class): bool
    {
        foreach ($class->getProperties() as $property) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if ($phpDocInfo->hasByAnnotationClass('JMS\Serializer\Annotation\Type')) {
                continue;
            }

            return false;
        }

        return true;
    }
}
