<?php

declare(strict_types=1);

namespace Rector\PHPUnit\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class TestsNodeAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var ObjectType[]
     */
    private $testCaseObjectTypes = [];

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        NodeNameResolver $nodeNameResolver,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;

        $this->testCaseObjectTypes = [
            new ObjectType('PHPUnit\Framework\TestCase'),
            new ObjectType('PHPUnit_Framework_TestCase'),
        ];
    }

    public function isInTestClass(Node $node): bool
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return false;
        }

        return $this->nodeTypeResolver->isObjectTypes($classLike, $this->testCaseObjectTypes);
    }

    public function isTestClassMethod(ClassMethod $classMethod): bool
    {
        if (! $classMethod->isPublic()) {
            return false;
        }

        if ($this->nodeNameResolver->isName($classMethod, 'test*')) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasByName('test');
    }

    public function isPHPUnitMethodName(Node $node, string $name): bool
    {
        if (! $this->isPHPUnitTestCaseCall($node)) {
            return false;
        }

        /** @var StaticCall|MethodCall $node */
        return $this->nodeNameResolver->isName($node->name, $name);
    }

    public function isPHPUnitTestCaseCall(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        return $node instanceof MethodCall || $node instanceof StaticCall;
    }

    /**
     * @param string[] $names
     */
    public function isPHPUnitMethodNames(Node $node, array $names): bool
    {
        if (! $this->isPHPUnitTestCaseCall($node)) {
            return false;
        }

        /** @var MethodCall|StaticCall $node */
        return $this->nodeNameResolver->isNames($node->name, $names);
    }
}
