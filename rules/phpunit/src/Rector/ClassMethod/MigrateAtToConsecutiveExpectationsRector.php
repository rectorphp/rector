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
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeFactory\ConsecutiveAssertionFactory;
use Rector\PHPUnit\ValueObject\ExpectationMock;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\MigrateAtToConsecutiveExpectationsRectorTest
 */
final class MigrateAtToConsecutiveExpectationsRector extends AbstractRector
{
    private const REPLACE_WILL_WITH = [
        'willReturnMap' => 'returnValueMap',
        'willReturnArgument' => 'returnArgument',
        'willReturnCallback' => 'returnCallback',
        'willThrowException' => 'throwException',
    ];

    /**
     * @var ConsecutiveAssertionFactory
     */
    private $consecutiveAssertionFactory;

    public function __construct(ConsecutiveAssertionFactory $consecutiveAssertionFactory)
    {
        $this->consecutiveAssertionFactory = $consecutiveAssertionFactory;
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

        $expectationMocks = [];
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if ($stmt->expr instanceof MethodCall) {
                $transformableWillStatements = [
                    'will',
                    'willReturn',
                    'willReturnReference',
                    'willReturnMap',
                    'willReturnArgument',
                    'willReturnCallback',
                    'willReturnSelf',
                    'willThrowException',
                ];
                if (!$stmt->expr->name instanceof Identifier) {
                    continue;
                }

                if (in_array($stmt->expr->name->name, $transformableWillStatements)) {
                    $willReturnValue = $this->getReturnExpr($stmt->expr);

                    /** @var MethodCall $method */
                    $method = $stmt->expr->var;
                } else {
                    $willReturnValue = null;
                    $method = $stmt->expr;
                }

                if (!$method->name instanceof Identifier) {
                    continue;
                }

                if ($method->name->name !== 'method') {
                    continue;
                }

                if (count($method->args) !== 1) {
                    continue;
                }

                /** @var MethodCall $maybeWith */
                $maybeWith = $method->var;
                if (!$maybeWith->name instanceof Identifier) {
                    continue;
                }

                if ($maybeWith->name->name === 'with') {
                    $withArgs = array_map(static function (Arg $arg) {
                        return $arg->value;
                    }, $maybeWith->args);
                    /** @var MethodCall $expects */
                    $expects = $maybeWith->var;
                } else {
                    $withArgs = [null];
                    /** @var MethodCall $expects */
                    $expects = $method->var;
                }

                if (!$expects->name instanceof Identifier) {
                    continue;
                }
                if ($expects->name->name !== 'expects') {
                    continue;
                }

                if (count($expects->args) === 1) {
                    $expectsArg = $expects->args[0];
                    /** @var MethodCall $expectsValue */
                    $expectsValue = $expectsArg->value;
                    if (!$expectsValue->name instanceof Identifier) {
                        continue;
                    }
                    if ($expectsValue->name->name !== 'at') {
                        continue;
                    }

                    if (count($expectsValue->args) === 1) {
                        $atArg = $expectsValue->args[0];
                        $atValue = $atArg->value;
                        if ($atValue instanceof LNumber && $expects->var instanceof Variable) {
                            $expectationMocks[] = new ExpectationMock($expects->var, $method->args, $atValue->value, $willReturnValue, $withArgs);
                        }
                    }
                }
            }
        }

        if (count($expectationMocks) > 0) {
            $entryCount = count($expectationMocks);
            $removalCounter = 1;
            foreach ($stmts as $stmt) {
                if (!$stmt instanceof Expression) {
                    continue;
                }
                if ($stmt->expr instanceof MethodCall) {
                    if ($removalCounter < $entryCount) {
                        $this->removeNode($stmt);
                        ++$removalCounter;
                    } else {
                        $stmt->expr = $this->buildNewExpectation($expectationMocks);
                    }
                }
            }
        }

        return $node;
    }

    /**
     * @param ExpectationMock[] $expectationMocks
     */
    private function buildNewExpectation(array $expectationMocks): MethodCall
    {
        $var = $expectationMocks[0]->getVar();
        $methodArguments = $expectationMocks[0]->getMethodArguments();
        $expectationMocks = $this->fillMissingAtIndexes($expectationMocks, $var);

        usort($expectationMocks, static function (ExpectationMock $expectationMockA, ExpectationMock $expectationMockB) {
            return $expectationMockA->getIndex() > $expectationMockB->getIndex() ? 1 : -1;
        });
        if ($this->hasReturnValue($expectationMocks)) {
            if ($this->hasWithValues($expectationMocks)) {
                return $this->consecutiveAssertionFactory->createWillReturnOnConsecutiveCalls(
                    $this->consecutiveAssertionFactory->createWithConsecutive(
                        $this->consecutiveAssertionFactory->createMethod(
                            $var,
                            $methodArguments
                        ),
                        $this->buildWithArgs($expectationMocks)
                    ),
                    $this->buildReturnArgs($expectationMocks)
                );
            }

            return $this->consecutiveAssertionFactory->createWillReturnOnConsecutiveCalls(
                $this->consecutiveAssertionFactory->createMethod(
                    $var,
                    $methodArguments
                ),
                $this->buildReturnArgs($expectationMocks)
            );
        }

        return $this->consecutiveAssertionFactory->createWithConsecutive(
            $this->consecutiveAssertionFactory->createMethod(
                $var,
                $methodArguments
            ),
            $this->buildWithArgs($expectationMocks)
        );
    }

    /**
     * @param ExpectationMock[] $expectationMocks
     */
    private function hasReturnValue(array $expectationMocks): bool
    {
        foreach ($expectationMocks as $expectationMock) {
            if ($expectationMock->getReturn() !== null) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param ExpectationMock[] $expectationMocks
     */
    private function hasWithValues(array $expectationMocks): bool
    {
        foreach ($expectationMocks as $expectationMock) {
            if (
                count($expectationMock->getWithArguments()) > 1
                || (
                    count($expectationMock->getWithArguments()) === 1
                    && $expectationMock->getWithArguments()[0] !== null
                )) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ExpectationMock[] $expectationMocks
     * @return Arg[]
     */
    private function buildReturnArgs(array $expectationMocks): array
    {
        return array_map(static function (ExpectationMock $expectationMock) {
            return new Arg($expectationMock->getReturn() ?: new ConstFetch(new Name('null')));
        }, $expectationMocks);
    }

    /**
     * @param ExpectationMock[] $expectationMocks
     * @return Arg[]
     */
    private function buildWithArgs(array $expectationMocks): array
    {
        return array_map(static function (ExpectationMock $expectationMock) {
            return new Arg(
                new Expr\Array_(
                    array_map(static function (?Expr $expr) {
                        return new ArrayItem($expr ?: new ConstFetch(new Name('null')));
                    }, $expectationMock->getWithArguments())
                )
            );
        }, $expectationMocks);
    }

    /**
     * @param ExpectationMock[] $expectationMocks
     * @return ExpectationMock[]
     */
    private function fillMissingAtIndexes(array $expectationMocks, Expr $var): array
    {
        $indexes = array_map(static function(ExpectationMock $expectationMock) { return $expectationMock->getIndex(); }, $expectationMocks);
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

    private function getReturnExpr(MethodCall $methodCall): Expr
    {
        if (!$methodCall->name instanceof Identifier) {
            return $methodCall;
        }

        $methodCallName = $methodCall->name->name;
        if ($methodCallName === 'will') {
            return $methodCall->args[0]->value;
        }

        if ($methodCallName === 'willReturnSelf') {
            return new MethodCall(
                new Variable('this'),
                new Identifier('returnSelf'),
                []
            );
        }

        if ($methodCallName === 'willReturnReference') {
            return new Expr\New_(
                new FullyQualified('\PHPUnit\Framework\MockObject\Stub\ReturnReference'),
                [new Arg($methodCall->args[0]->value)]
            );
        }

        if (array_key_exists($methodCallName, self::REPLACE_WILL_WITH)) {
            return new MethodCall(
                new Variable('this'),
                new Identifier(self::REPLACE_WILL_WITH[$methodCallName]),
                [new Arg($methodCall->args[0]->value)]
            );
        }

        return $methodCall->args[0]->value;
    }
}
