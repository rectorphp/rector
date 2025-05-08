<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\Reflection\ReflectionResolver;
final class TestsNodeAnalyzer
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @var string[]
     */
    private const TEST_CASE_OBJECT_CLASSES = ['PHPUnit\\Framework\\TestCase', 'PHPUnit_Framework_TestCase'];
    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver, PhpDocInfoFactory $phpDocInfoFactory, ReflectionResolver $reflectionResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isInTestClass(Node $node) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        foreach (self::TEST_CASE_OBJECT_CLASSES as $testCaseObjectClass) {
            if ($classReflection->is($testCaseObjectClass)) {
                return \true;
            }
        }
        return \false;
    }
    public function isTestClassMethod(ClassMethod $classMethod) : bool
    {
        if (!$classMethod->isPublic()) {
            return \false;
        }
        if (\strncmp($classMethod->name->toString(), 'test', \strlen('test')) === 0) {
            return \true;
        }
        foreach ($classMethod->getAttrGroups() as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($attribute->name->toString() === 'PHPUnit\\Framework\\Attributes\\Test') {
                    return \true;
                }
            }
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasByName('test');
    }
    public function isAssertMethodCallName(Node $node, string $name) : bool
    {
        if ($node instanceof StaticCall) {
            $callerType = $this->nodeTypeResolver->getType($node->class);
        } elseif ($node instanceof MethodCall) {
            $callerType = $this->nodeTypeResolver->getType($node->var);
        } else {
            return \false;
        }
        $assertObjectType = new ObjectType('PHPUnit\\Framework\\Assert');
        if (!$assertObjectType->isSuperTypeOf($callerType)->yes()) {
            return \false;
        }
        /** @var StaticCall|MethodCall $node */
        return $this->nodeNameResolver->isName($node->name, $name);
    }
    /**
     * @param string[] $names
     */
    public function isPHPUnitMethodCallNames(Node $node, array $names) : bool
    {
        if (!$this->isPHPUnitTestCaseCall($node)) {
            return \false;
        }
        /** @var MethodCall|StaticCall $node */
        return $this->nodeNameResolver->isNames($node->name, $names);
    }
    public function isPHPUnitTestCaseCall(Node $node) : bool
    {
        if ($node instanceof MethodCall) {
            return $this->isInTestClass($node);
        }
        if ($node instanceof StaticCall) {
            $classType = $this->nodeTypeResolver->getType($node->class);
            if ($classType instanceof ObjectType) {
                if ($classType->isInstanceOf(PHPUnitClassName::TEST_CASE)->yes()) {
                    return \true;
                }
                if ($classType->isInstanceOf(PHPUnitClassName::ASSERT)->yes()) {
                    return \true;
                }
            }
        }
        return \false;
    }
}
