<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\NodeAnalyzer\SetUpMethodDecorator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/3975#issuecomment-562584609
 *
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector\ConstructClassMethodToSetUpTestCaseRectorTest
 */
final class ConstructClassMethodToSetUpTestCaseRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\SetUpMethodDecorator
     */
    private $setUpMethodDecorator;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ClassAnalyzer $classAnalyzer, VisibilityManipulator $visibilityManipulator, SetUpMethodDecorator $setUpMethodDecorator)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->classAnalyzer = $classAnalyzer;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->setUpMethodDecorator = $setUpMethodDecorator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change __construct() method in tests of `PHPUnit\\Framework\\TestCase` to setUp(), to prevent dangerous override', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someValue;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        $this->someValue = 1000;
        parent::__construct($name, $data, $dataName);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someValue;

    protected function setUp()
    {
        parent::setUp();

        $this->someValue = 1000;
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
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }
        $addedStmts = $this->resolveStmtsToAddToSetUp($constructClassMethod);
        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            // no setUp() method yet, rename it to setUp :)
            $constructClassMethod->name = new Identifier(MethodName::SET_UP);
            $constructClassMethod->params = [];
            $constructClassMethod->stmts = $addedStmts;
            $this->setUpMethodDecorator->decorate($constructClassMethod);
            $this->visibilityManipulator->makeProtected($constructClassMethod);
        } else {
            $stmtKey = $constructClassMethod->getAttribute(AttributeKey::STMT_KEY);
            unset($node->stmts[$stmtKey]);
            $setUpClassMethod->stmts = \array_merge((array) $setUpClassMethod->stmts, $addedStmts);
        }
        return $node;
    }
    /**
     * @return Stmt[]
     */
    private function resolveStmtsToAddToSetUp(ClassMethod $constructClassMethod) : array
    {
        $constructorStmts = (array) $constructClassMethod->stmts;
        // remove parent call
        foreach ($constructorStmts as $key => $constructorStmt) {
            if ($constructorStmt instanceof Expression) {
                $constructorStmt = clone $constructorStmt->expr;
            }
            if (!$this->isParentCallNamed($constructorStmt, MethodName::CONSTRUCT)) {
                continue;
            }
            unset($constructorStmts[$key]);
        }
        return $constructorStmts;
    }
    private function isParentCallNamed(Node $node, string $desiredMethodName) : bool
    {
        if (!$node instanceof StaticCall) {
            return \false;
        }
        if ($node->class instanceof Expr) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->class, 'parent')) {
            return \false;
        }
        if ($node->name instanceof Expr) {
            return \false;
        }
        return $this->nodeNameResolver->isName($node->name, $desiredMethodName);
    }
}
