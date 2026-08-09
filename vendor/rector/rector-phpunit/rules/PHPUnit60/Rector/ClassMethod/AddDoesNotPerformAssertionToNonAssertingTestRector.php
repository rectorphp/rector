<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit60\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitor;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PHPUnit\Enum\PHPUnitAttribute;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\AssertCallAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\MockedVariableAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://phpunit.readthedocs.io/en/9.5/annotations.html#doesnotperformassertions
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/2484
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\AddDoesNotPerformAssertionToNonAssertingTestRectorTest
 */
final class AddDoesNotPerformAssertionToNonAssertingTestRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private AssertCallAnalyzer $assertCallAnalyzer;
    /**
     * @readonly
     */
    private MockedVariableAnalyzer $mockedVariableAnalyzer;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * inherited from the PHPUnit 6.0 set
     */
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('phpunit/phpunit', '>=6.0');
    }
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, AssertCallAnalyzer $assertCallAnalyzer, MockedVariableAnalyzer $mockedVariableAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, ReflectionResolver $reflectionResolver, ReflectionProvider $reflectionProvider)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->assertCallAnalyzer = $assertCallAnalyzer;
        $this->mockedVariableAnalyzer = $mockedVariableAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionResolver = $reflectionResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Tests without assertion will have #[DoesNotPerformAssertions] attribute, or @doesNotPerformAssertions annotation on PHPUnit below 10', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $nothing = 5;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function test()
    {
        $nothing = 5;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }
        $this->removeAddToAssertionCountCalls($node);
        // the attribute is available since PHPUnit 10, prefer it over the annotation
        if ($this->reflectionProvider->hasClass(PHPUnitAttribute::DOES_NOT_PERFORM_ASSERTIONS)) {
            $node->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(PHPUnitAttribute::DOES_NOT_PERFORM_ASSERTIONS))]);
            return $node;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@doesNotPerformAssertions', new GenericTagValueNode('')));
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($classMethod)) {
            return \true;
        }
        if (!$this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
            return \true;
        }
        if ($classMethod->isAbstract()) {
            return \true;
        }
        // we have no idea how the trait is used, the using class can assert on its own
        if ($this->isInTrait($classMethod)) {
            return \true;
        }
        // the parent test case asserts in its own integration methods
        if ($this->isInTwigIntegrationTestCase($classMethod)) {
            return \true;
        }
        if ($this->hasAssertingAnnotationOrAttribute($classMethod)) {
            return \true;
        }
        $this->assertCallAnalyzer->resetNesting();
        if ($this->assertCallAnalyzer->containsAssertCall($classMethod)) {
            return \true;
        }
        return $this->mockedVariableAnalyzer->containsMockAsUsedVariable($classMethod);
    }
    /**
     * The assertion count fakes an assertion, but the "@doesNotPerformAssertions" annotation makes it obsolete
     */
    private function removeAddToAssertionCountCalls(ClassMethod $classMethod): void
    {
        $hasJustRemovedCall = \false;
        $this->traverseNodesWithCallable($classMethod, function (Node $node) use (&$hasJustRemovedCall): ?int {
            // a comment on the same line as the removed call is parsed as a nop statement right behind it
            if ($hasJustRemovedCall && $node instanceof Nop) {
                return NodeVisitor::REMOVE_NODE;
            }
            $hasJustRemovedCall = \false;
            if (!$this->isAddToAssertionCountExpression($node)) {
                return null;
            }
            $hasJustRemovedCall = \true;
            return NodeVisitor::REMOVE_NODE;
        });
    }
    private function isAddToAssertionCountExpression(Node $node): bool
    {
        if (!$node instanceof Expression) {
            return \false;
        }
        if (!$node->expr instanceof MethodCall) {
            return \false;
        }
        $methodCall = $node->expr;
        if (!$this->isName($methodCall->var, 'this')) {
            return \false;
        }
        return $this->isName($methodCall->name, 'addToAssertionCount');
    }
    private function isInTrait(ClassMethod $classMethod): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->isTrait();
    }
    private function isInTwigIntegrationTestCase(ClassMethod $classMethod): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->is(PHPUnitClassName::TWIG_INTEGRATION_TEST_CASE);
    }
    private function hasAssertingAnnotationOrAttribute(ClassMethod $classMethod): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        if ($phpDocInfo->hasByNames(['doesNotPerformAssertions', 'expectedException'])) {
            return \true;
        }
        return $this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, PHPUnitAttribute::DOES_NOT_PERFORM_ASSERTIONS);
    }
}
