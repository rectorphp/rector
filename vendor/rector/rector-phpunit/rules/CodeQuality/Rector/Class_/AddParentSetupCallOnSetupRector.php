<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\AddParentSetupCallOnSetupRector\AddParentSetupCallOnSetupRectorTest
 */
final class AddParentSetupCallOnSetupRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add missing parent::setUp() call on setUp() method on test class', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    protected function setUp(): void
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $setUpMethod = $node->getMethod(MethodName::SET_UP);
        if (!$setUpMethod instanceof ClassMethod) {
            return null;
        }
        if ($setUpMethod->isAbstract() || $setUpMethod->stmts === null) {
            return null;
        }
        $isSetupExists = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($setUpMethod, function (Node $subNode) : bool {
            if (!$subNode instanceof StaticCall) {
                return \false;
            }
            if (!$this->isName($subNode->class, 'parent')) {
                return \false;
            }
            return $this->isName($subNode->name, 'setUp');
        });
        if ($isSetupExists) {
            return null;
        }
        $setUpMethod->stmts = \array_merge([new Expression(new StaticCall(new Name('parent'), new Identifier('setUp')))], $setUpMethod->stmts);
        return $node;
    }
}
