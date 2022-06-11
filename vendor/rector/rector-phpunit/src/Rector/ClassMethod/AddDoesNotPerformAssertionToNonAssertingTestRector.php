<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\AssertCallAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\MockedVariableAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://phpunit.readthedocs.io/en/7.3/annotations.html#doesnotperformassertions
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/2484
 *
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\AddDoesNotPerformAssertionToNonAssertingTestRectorTest
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
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, AssertCallAnalyzer $assertCallAnalyzer, MockedVariableAnalyzer $mockedVariableAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->assertCallAnalyzer = $assertCallAnalyzer;
        $this->mockedVariableAnalyzer = $mockedVariableAnalyzer;
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
        $this->addDoesNotPerformAssertions($node);
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
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        if ($phpDocInfo->hasByNames(['doesNotPerformAssertions', 'expectedException'])) {
            return \true;
        }
        $this->assertCallAnalyzer->resetNesting();
        if ($this->assertCallAnalyzer->containsAssertCall($classMethod)) {
            return \true;
        }
        return $this->mockedVariableAnalyzer->containsMockAsUsedVariable($classMethod);
    }
    private function addDoesNotPerformAssertions(ClassMethod $classMethod) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@doesNotPerformAssertions', new GenericTagValueNode('')));
    }
}
