<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\ExpectationAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFactory\ConsecutiveAssertionFactory;
use Rector\PHPUnit\ValueObject\ExpectationMock;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\MigrateAtToConsecutiveExpectationsRectorTest
 */
final class MigrateAtToConsecutiveExpectationsRector extends AbstractRector
{
    private const PROCESSABLE_WILL_STATEMENTS = [
        'will',
        'willReturn',
        'willReturnReference',
        'willReturnMap',
        'willReturnArgument',
        'willReturnCallback',
        'willReturnSelf',
        'willThrowException',
    ];

    /**
     * @var ConsecutiveAssertionFactory
     */
    private $consecutiveAssertionFactory;

    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    /**
     * @var ExpectationAnalyzer
     */
    private $expectationAnalyzer;

    public function __construct(
        ConsecutiveAssertionFactory $consecutiveAssertionFactory,
        TestsNodeAnalyzer $testsNodeAnalyzer,
        ExpectationAnalyzer $expectationAnalyzer
    )
    {
        $this->consecutiveAssertionFactory = $consecutiveAssertionFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->expectationAnalyzer = $expectationAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrates deprecated $this->at to $this->withConsecutive and $this->willReturnOnConsecutiveCalls',
            [
                new CodeSample(
                    '',
                    ''
                ),
            ]
        );
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
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }

        $expressions = array_filter($stmts, function (Stmt $expr) {
            return $stmt instanceof Expression && $stmt->expr instanceof MethodCall;
        });

        $expectationMocks = $this->getExpectationMocks($expressions);

        if (count($expectationMocks) === 0) {
            return null;
        }

        $this->replaceExpectationNodes($expectationMocks, $expressions);

        return $node;
    }

    /**
     * @param ExpectationMock[] $expectationMocks
     */
    private function buildNewExpectation(array $expectationMocks): MethodCall
    {
        $var = $expectationMocks[0]->getExpectationVariable();
        $expectationMocks = $this->fillMissingAtIndexes($expectationMocks, $var);
        return $this->consecutiveAssertionFactory->createAssertionFromExpectationMocks($expectationMocks);
    }

    /**
     * @param ExpectationMock[] $expectationMocks
     * @return ExpectationMock[]
     */
    private function fillMissingAtIndexes(array $expectationMocks, Variable $var): array
    {
        $indexes = array_map(static function (ExpectationMock $expectationMock) {
            return $expectationMock->getIndex();
        }, $expectationMocks);
        $minIndex = min($indexes);
        $maxIndex = max($indexes);

        // 0,1,2,3,4
        // min = 0 ; max = 4 ; count = 5
        // OK

        // 1,2,3,4
        // min = 1 ; max = 4 ; count = 4
        // ADD 0

        // OR

        // 3
        // min = 3; max = 3 ; count = 1
        // 0,1,2
        if ($minIndex !== 0) {
            for ($i = 0; $i < $minIndex; ++$i) {
                $expectationMocks[] = new ExpectationMock($var, [], $i, null, []);
            }
            $minIndex = 0;
        }

        // 0,1,2,4
        // min = 0 ; max = 4 ; count = 4
        // ADD 3
        if (($maxIndex - $minIndex + 1) !== count($expectationMocks)) {
            $existingIndexes = array_column($expectationMocks, 'index');
            for ($i = 0; $i < $maxIndex; ++$i) {
                if (!in_array($i, $existingIndexes, true)) {
                    $expectationMocks[] = new ExpectationMock($var, [], $i, null, []);
                }
            }
        }
        return $expectationMocks;
    }

    /**
     * @param Expression[] $stmts
     * @return ExpectationMock[]
     */
    private function getExpectationMocks(array $stmts): array
    {
        $expectationMocks = [];
        foreach ($stmts as $stmt) {
            /** @var MethodCall $expr */
            $expr = $stmt->expr;
            $method = $this->getMethod($expr);
            if (!$this->testsNodeAnalyzer->isPHPUnitMethodName($method, 'method')) {
                continue;
            }

            /** @var MethodCall $expects */
            $expects = $this->getExpects($method->var, $method);
            if (!$this->expectationAnalyzer->isValidExpectsCall($expects)) {
                continue;
            }

            $expectsArg = $expects->args[0];
            /** @var MethodCall $expectsValue */
            $expectsValue = $expectsArg->value;
            if (!$this->expectationAnalyzer->isValidAtCall($expectsValue)) {
                continue;
            }

            $atArg = $expectsValue->args[0];
            $atValue = $atArg->value;
            if ($atValue instanceof LNumber && $expects->var instanceof Variable) {
                $expectationMocks[] = new ExpectationMock(
                    $expects->var,
                    $method->args,
                    $atValue->value,
                    $this->getWill($expr),
                    $this->getWithArgs($method->var)
                );
            }
        }
        return $expectationMocks;
    }

    /**
     * @param ExpectationMock[] $expectationMocks
     * @param Expression[] $stmts
     */
    private function replaceExpectationNodes(array $expectationMocks, array $stmts): void
    {
        $entryCount = count($expectationMocks);
        $removalCounter = 1;
        foreach ($stmts as $stmt) {
            if ($removalCounter < $entryCount) {
                $this->removeNode($stmt);
                ++$removalCounter;
            } else {
                $stmt->expr = $this->buildNewExpectation($expectationMocks);
            }
        }
    }

    private function getMethod(MethodCall $expr): MethodCall
    {
        if ($this->testsNodeAnalyzer->isPHPUnitMethodNames($expr, self::PROCESSABLE_WILL_STATEMENTS) && $expr->var instanceof MethodCall) {
            return $expr->var;
        }

        return $expr;
    }

    private function getWill(MethodCall $expr): ?Expr
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodNames($expr, self::PROCESSABLE_WILL_STATEMENTS)) {
            return null;
        }

        return $this->consecutiveAssertionFactory->createWillReturn($expr);
    }

    private function getExpects(Expr $maybeWith, MethodCall $method): Expr
    {
        if ($this->testsNodeAnalyzer->isPHPUnitMethodName($maybeWith, 'with') && $maybeWith instanceof MethodCall) {
            return $maybeWith->var;
        }

        return $method->var;
    }

    /**
     * @return array<int, Expr|null>
     */
    private function getWithArgs(Expr $maybeWith): array
    {
        if ($this->testsNodeAnalyzer->isPHPUnitMethodName($maybeWith, 'with') && $maybeWith instanceof MethodCall) {
            return array_map(static function (Arg $arg) {
                return $arg->value;
            }, $maybeWith->args);
        }

        return [null];
    }
}
