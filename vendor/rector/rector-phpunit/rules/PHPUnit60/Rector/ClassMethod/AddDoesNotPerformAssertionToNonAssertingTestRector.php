<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit60\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Core\Rector\AbstractRector;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\AssertCallAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\MockedVariableAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://phpunit.readthedocs.io/en/9.5/annotations.html#doesnotperformassertions
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/2484
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\AddDoesNotPerformAssertionToNonAssertingTestRectorTest
 */
final class AddDoesNotPerformAssertionToNonAssertingTestRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\AssertCallAnalyzer
     */
    private $assertCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\MockedVariableAnalyzer
     */
    private $mockedVariableAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, AssertCallAnalyzer $assertCallAnalyzer, MockedVariableAnalyzer $mockedVariableAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->assertCallAnalyzer = $assertCallAnalyzer;
        $this->mockedVariableAnalyzer = $mockedVariableAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Tests without assertion will have @doesNotPerformAssertion', [new CodeSample(<<<'CODE_SAMPLE'
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
    /**
     * @doesNotPerformAssertions
     */
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
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@doesNotPerformAssertions', new GenericTagValueNode('')));
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod) : bool
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
        if ($this->hasAssertingAnnotationOrAttribute($classMethod)) {
            return \true;
        }
        $this->assertCallAnalyzer->resetNesting();
        if ($this->assertCallAnalyzer->containsAssertCall($classMethod)) {
            return \true;
        }
        return $this->mockedVariableAnalyzer->containsMockAsUsedVariable($classMethod);
    }
    private function hasAssertingAnnotationOrAttribute(ClassMethod $classMethod) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        if ($phpDocInfo->hasByNames(['doesNotPerformAssertions', 'expectedException'])) {
            return \true;
        }
        return $this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, 'PHPUnit\\Framework\\Attributes\\DoesNotPerformAssertions');
    }
}
