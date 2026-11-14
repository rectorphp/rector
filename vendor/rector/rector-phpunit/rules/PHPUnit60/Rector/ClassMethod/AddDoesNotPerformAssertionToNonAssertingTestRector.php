<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit60\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitor;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPUnit\Enum\PHPUnitAttribute;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\AssertCallAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\LifecycleMethodAssertAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\MockedVariableAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
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
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private LifecycleMethodAssertAnalyzer $lifecycleMethodAssertAnalyzer;
    /**
     * inherited from the PHPUnit 6.0 set
     */
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('phpunit/phpunit', '>=6.0');
    }
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, AssertCallAnalyzer $assertCallAnalyzer, MockedVariableAnalyzer $mockedVariableAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider, LifecycleMethodAssertAnalyzer $lifecycleMethodAssertAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->assertCallAnalyzer = $assertCallAnalyzer;
        $this->mockedVariableAnalyzer = $mockedVariableAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->lifecycleMethodAssertAnalyzer = $lifecycleMethodAssertAnalyzer;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        // a child class can add asserting lifecycle methods, that count toward the inherited tests
        if ($node->isAbstract()) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        // the parent test case asserts in its own integration methods
        if ($this->isInTwigIntegrationTestCase($scope)) {
            return null;
        }
        // assertions in lifecycle methods count toward every test of the class
        if ($this->lifecycleMethodAssertAnalyzer->hasAssertingLifecycleMethod($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }
            $this->removeAddToAssertionCountCalls($classMethod);
            $this->addDoesNotPerformAssertions($classMethod);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function addDoesNotPerformAssertions(ClassMethod $classMethod): void
    {
        // the attribute is available since PHPUnit 10, prefer it over the annotation
        if ($this->reflectionProvider->hasClass(PHPUnitAttribute::DOES_NOT_PERFORM_ASSERTIONS)) {
            $classMethod->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(PHPUnitAttribute::DOES_NOT_PERFORM_ASSERTIONS))]);
            return;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@doesNotPerformAssertions', new GenericTagValueNode('')));
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if (!$this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
            return \true;
        }
        if ($classMethod->isAbstract()) {
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
    private function isInTwigIntegrationTestCase(Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
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
