<?php

declare (strict_types=1);
namespace Rector\Autodiscovery\Analyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ValueObjectClassAnalyzer
{
    /**
     * @var array<string, bool>
     */
    private $valueObjectStatusByClassName = [];
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->astResolver = $astResolver;
        $this->classAnalyzer = $classAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isValueObjectClass(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return \false;
        }
        /** @var string $className */
        $className = (string) $this->nodeNameResolver->getName($class);
        if (isset($this->valueObjectStatusByClassName[$className])) {
            return $this->valueObjectStatusByClassName[$className];
        }
        $constructClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $this->hasExlusivelySerializeProperties($class, $className);
        }
        // resolve constructor types
        foreach ($constructClassMethod->params as $param) {
            $paramType = $this->nodeTypeResolver->getType($param);
            if (!$paramType instanceof \PHPStan\Type\ObjectType) {
                continue;
            }
            // awesome!
            // is it services or value object?
            $paramTypeClass = $this->astResolver->resolveClassFromName($paramType->getClassName());
            if (!$paramTypeClass instanceof \PhpParser\Node\Stmt\Class_) {
                // not sure :/
                continue;
            }
            if (!$this->isValueObjectClass($paramTypeClass)) {
                return \false;
            }
        }
        // if we didn't prove it's not a value object so far → fallback to true
        $this->valueObjectStatusByClassName[$className] = \true;
        return \true;
    }
    private function hasExlusivelySerializeProperties(\PhpParser\Node\Stmt\Class_ $class, string $className) : bool
    {
        // A. has all properties with serialize?
        if ($this->hasAllPropertiesWithSerialize($class)) {
            $this->valueObjectStatusByClassName[$className] = \true;
            return \true;
        }
        // probably not a value object
        $this->valueObjectStatusByClassName[$className] = \false;
        return \false;
    }
    private function hasAllPropertiesWithSerialize(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        foreach ($class->getProperties() as $property) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if ($phpDocInfo->hasByAnnotationClass('JMS\\Serializer\\Annotation\\Type')) {
                continue;
            }
            return \false;
        }
        return \true;
    }
}
