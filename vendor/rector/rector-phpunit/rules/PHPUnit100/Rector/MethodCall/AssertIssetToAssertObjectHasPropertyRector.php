<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit100\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\TypeWithClassName;
use Rector\PHPUnit\Enum\AssertMethod;
use Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ClassReflectionAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit100\Rector\MethodCall\AssertIssetToAssertObjectHasPropertyRector\AssertIssetToAssertObjectHasPropertyRectorTest
 *
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/5220
 */
final class AssertIssetToAssertObjectHasPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator
     */
    private $identifierManipulator;
    /**
     * @readonly
     * @var \Rector\Reflection\ClassReflectionAnalyzer
     */
    private $classReflectionAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, IdentifierManipulator $identifierManipulator, ClassReflectionAnalyzer $classReflectionAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->identifierManipulator = $identifierManipulator;
        $this->classReflectionAnalyzer = $classReflectionAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change "isset()" property check, to assertObjectHasProperty() method', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $object = new stdClass();
        $this->assertTrue(isset($object->someProperty));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $object = new stdClass();
        $this->assertObjectHasProperty('someProperty', $object);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, [AssertMethod::ASSERT_TRUE, AssertMethod::ASSERT_FALSE])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $firstArgValue = $firstArg->value;
        if (!$firstArgValue instanceof Isset_) {
            return null;
        }
        $issetExpr = $firstArgValue->vars[0];
        if (!$issetExpr instanceof PropertyFetch) {
            return null;
        }
        if ($this->hasMagicIsset($issetExpr->var)) {
            return null;
        }
        $name = $this->getName($issetExpr);
        if ($name === null) {
            return null;
        }
        $this->identifierManipulator->renameNodeWithMap($node, [AssertMethod::ASSERT_TRUE => 'assertObjectHasProperty', AssertMethod::ASSERT_FALSE => 'assertObjectNotHasProperty']);
        $oldArgs = $node->getArgs();
        unset($oldArgs[0]);
        $newArgs = $this->nodeFactory->createArgs([new String_($name), $issetExpr->var]);
        $node->args = \array_merge($newArgs, $oldArgs);
        return $node;
    }
    private function hasMagicIsset(Expr $expr) : bool
    {
        $type = $this->nodeTypeResolver->getType($expr);
        if (!$type instanceof TypeWithClassName) {
            // object not found, skip
            return $type instanceof ObjectWithoutClassType;
        }
        $classReflection = $type->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if ($classReflection->hasMethod('__isset')) {
            return \true;
        }
        if ($classReflection->hasMethod('__get')) {
            return \true;
        }
        if (!$classReflection->isClass()) {
            return \false;
        }
        return $this->classReflectionAnalyzer->resolveParentClassName($classReflection) !== null;
    }
}
