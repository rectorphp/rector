<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Nette\NodeAnalyzer\StaticCallAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeManipulator\SetUpClassMethodNodeManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3975#issuecomment-562584609
 *
 * @see \Rector\PHPUnit\Tests\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector\ConstructClassMethodToSetUpTestCaseRectorTest
 */
final class ConstructClassMethodToSetUpTestCaseRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeManipulator\SetUpClassMethodNodeManipulator
     */
    private $setUpClassMethodNodeManipulator;
    /**
     * @readonly
     * @var \Rector\Nette\NodeAnalyzer\StaticCallAnalyzer
     */
    private $staticCallAnalyzer;
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
    public function __construct(\Rector\PHPUnit\NodeManipulator\SetUpClassMethodNodeManipulator $setUpClassMethodNodeManipulator, \Rector\Nette\NodeAnalyzer\StaticCallAnalyzer $staticCallAnalyzer, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer)
    {
        $this->setUpClassMethodNodeManipulator = $setUpClassMethodNodeManipulator;
        $this->staticCallAnalyzer = $staticCallAnalyzer;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->classAnalyzer = $classAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change __construct() method in tests of `PHPUnit\\Framework\\TestCase` to setUp(), to prevent dangerous override', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $constructClassMethod = $node->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }
        $this->removeNode($constructClassMethod);
        $addedStmts = $this->resolveStmtsToAddToSetUp($constructClassMethod);
        $this->setUpClassMethodNodeManipulator->decorateOrCreate($node, $addedStmts);
        return $node;
    }
    /**
     * @return Stmt[]
     */
    private function resolveStmtsToAddToSetUp(\PhpParser\Node\Stmt\ClassMethod $constructClassMethod) : array
    {
        $constructorStmts = (array) $constructClassMethod->stmts;
        // remove parent call
        foreach ($constructorStmts as $key => $constructorStmt) {
            if ($constructorStmt instanceof \PhpParser\Node\Stmt\Expression) {
                $constructorStmt = clone $constructorStmt->expr;
            }
            if (!$this->staticCallAnalyzer->isParentCallNamed($constructorStmt, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
                continue;
            }
            unset($constructorStmts[$key]);
        }
        return $constructorStmts;
    }
}
