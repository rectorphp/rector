<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertIssetToSpecificMethodRector\AssertIssetToSpecificMethodRectorTest
 */
final class AssertIssetToSpecificMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const ASSERT_TRUE = 'assertTrue';
    /**
     * @var string
     */
    private const ASSERT_FALSE = 'assertFalse';
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator
     */
    private $identifierManipulator;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator $identifierManipulator, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer, \Rector\Core\PhpParser\AstResolver $astResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->astResolver = $astResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns isset comparisons to their method name alternatives in PHPUnit TestCase', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertTrue(isset($anything->foo));', '$this->assertObjectHasAttribute("foo", $anything);'), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertFalse(isset($anything["foo"]), "message");', '$this->assertArrayNotHasKey("foo", $anything, "message");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, [self::ASSERT_TRUE, self::ASSERT_FALSE])) {
            return null;
        }
        $firstArgumentValue = $node->args[0]->value;
        // is property access
        if (!$firstArgumentValue instanceof \PhpParser\Node\Expr\Isset_) {
            return null;
        }
        $variableNodeClass = \get_class($firstArgumentValue->vars[0]);
        if (!\in_array($variableNodeClass, [\PhpParser\Node\Expr\ArrayDimFetch::class, \PhpParser\Node\Expr\PropertyFetch::class], \true)) {
            return null;
        }
        /** @var Isset_ $issetNode */
        $issetNode = $node->args[0]->value;
        $issetNodeArg = $issetNode->vars[0];
        if ($issetNodeArg instanceof \PhpParser\Node\Expr\PropertyFetch) {
            if ($this->hasMagicIsset($issetNodeArg->var)) {
                return null;
            }
            return $this->refactorPropertyFetchNode($node, $issetNodeArg);
        }
        if ($issetNodeArg instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return $this->refactorArrayDimFetchNode($node, $issetNodeArg);
        }
        return $node;
    }
    private function hasMagicIsset(\PhpParser\Node $node) : bool
    {
        $resolved = $this->nodeTypeResolver->getType($node);
        if (!$resolved instanceof \PHPStan\Type\TypeWithClassName) {
            // object not found, skip
            return $resolved instanceof \PHPStan\Type\ObjectWithoutClassType;
        }
        $reflection = $resolved->getClassReflection();
        if (!$reflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        if ($reflection->hasMethod('__isset')) {
            return \true;
        }
        // reflection->getParents() got empty array when
        // extends class not found by PHPStan
        $className = $reflection->getName();
        $class = $this->astResolver->resolveClassFromName($className);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        if (!$class->extends instanceof \PhpParser\Node\Name\FullyQualified) {
            return \false;
        }
        // if parent class not detected by PHPStan, assume it has __isset
        return !$this->reflectionProvider->hasClass($class->extends->toString());
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function refactorPropertyFetchNode($node, \PhpParser\Node\Expr\PropertyFetch $propertyFetch) : ?\PhpParser\Node
    {
        $name = $this->getName($propertyFetch);
        if ($name === null) {
            return null;
        }
        $this->identifierManipulator->renameNodeWithMap($node, [self::ASSERT_TRUE => 'assertObjectHasAttribute', self::ASSERT_FALSE => 'assertObjectNotHasAttribute']);
        $oldArgs = $node->args;
        unset($oldArgs[0]);
        $newArgs = $this->nodeFactory->createArgs([new \PhpParser\Node\Scalar\String_($name), $propertyFetch->var]);
        $node->args = $this->appendArgs($newArgs, $oldArgs);
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function refactorArrayDimFetchNode($node, \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : \PhpParser\Node
    {
        $this->identifierManipulator->renameNodeWithMap($node, [self::ASSERT_TRUE => 'assertArrayHasKey', self::ASSERT_FALSE => 'assertArrayNotHasKey']);
        $oldArgs = $node->args;
        unset($oldArgs[0]);
        $node->args = \array_merge($this->nodeFactory->createArgs([$arrayDimFetch->dim, $arrayDimFetch->var]), $oldArgs);
        return $node;
    }
}
