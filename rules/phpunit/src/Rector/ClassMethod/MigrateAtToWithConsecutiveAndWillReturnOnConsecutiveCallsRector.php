<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PHPParser\Node\Expr\ArrayItem;
use PHPParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PHPParser\Node\Expr\Variable;
use PHPParser\Node\Name;
use PHPParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\ClassMethod;
use PHPParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeFactory\ConsecutiveAssertionFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector\AssertTrueFalseToSpecificMethodRectorTest
 */
final class MigrateAtToWithConsecutiveAndWillReturnOnConsecutiveCallsRector extends AbstractRector
{
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
        $mockEntries = [];
        $stmts = $node->stmts;
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if ($stmt->expr instanceof MethodCall) {
                if ($stmt->expr->name->name === 'willReturn') {
                    $willReturn = $stmt->expr;
                    if ($willReturn->name->name !== 'willReturn') {
                        continue;
                    }
                    if (count($willReturn->args) !== 1) {
                        continue;
                    }
                    $willReturnArg = $willReturn->args[0];
                    $willReturnValue = $willReturnArg->value;

                    /** @var MethodCall $method */
                    $method = $willReturn->var;
                } else {
                    $willReturnValue = null;
                    $method = $stmt->expr;
                }

                if ($method->name->name !== 'method') {
                    continue;
                }
                if (count($method->args) !== 1) {
                    continue;
                }

                /** @var MethodCall $maybeWith */
                $maybeWith = $method->var;
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
                if ($expects->name->name !== 'expects') {
                    continue;
                }

                if (count($expects->args) === 1) {
                    $expectsArg = $expects->args[0];
                    /** @var MethodCall $expectsValue */
                    $expectsValue = $expectsArg->value;
                    if ($expectsValue->name->name !== 'at') {
                        continue;
                    }

                    if (count($expectsValue->args) === 1) {
                        $atArg = $expectsValue->args[0];
                        $atValue = $atArg->value;
                        if ($atValue instanceof LNumber && $expects->var instanceof Variable) {
                            $mockEntries[] = [
                                'var' => $expects->var,
                                'methodArgs' => $method->args,
                                'index' => $atValue->value,
                                'return' => $willReturnValue,
                                'with' => $withArgs,
                            ];
                        }
                    }
                }
            }
        }

        if (count($mockEntries) > 0) {
            $entryCount = count($mockEntries);
            $removalCounter = 1;
            foreach ($stmts as $stmt) {
                if (!$stmt instanceof Expression) {
                    continue;
                }
                if ($stmt->expr instanceof MethodCall) {
                    if ($removalCounter < $entryCount) {
                        $this->removeNode($stmt);
                        $removalCounter++;
                    } else {
                        $stmt->expr = $this->buildNewExpectation($mockEntries);
                    }
                }
            }
        }

        return $node;
    }

    private function buildNewExpectation(array $mockEntries): MethodCall
    {
        $var = $mockEntries[0]['var'];
        $mockEntries = $this->fillMissingAtIndixes($mockEntries, $var);

        usort($mockEntries, static function ($mockEntryA, $mockEntryB) {
            return $mockEntryA['index'] > $mockEntryB['index'];
        });
        if ($this->hasReturnValue($mockEntries)) {
            if ($this->hasWithValues($mockEntries)) {
                return $this->consecutiveAssertionFactory->createWillReturnOnConsecutiveCalls(
                    $this->consecutiveAssertionFactory->createWithConsecutive(
                        $this->consecutiveAssertionFactory->createMethod(
                            $mockEntries[0]['var'],
                            $mockEntries[0]['methodArgs']
                        ),
                        $this->buildWithArgs($mockEntries)
                    ),
                    $this->buildReturnArgs($mockEntries)
                );
            }

            return $this->consecutiveAssertionFactory->createWillReturnOnConsecutiveCalls(
                $this->consecutiveAssertionFactory->createMethod(
                    $mockEntries[0]['var'],
                    $mockEntries[0]['methodArgs']
                ),
                $this->buildReturnArgs($mockEntries)
            );
        }

        return $this->consecutiveAssertionFactory->createWithConsecutive(
            $this->consecutiveAssertionFactory->createMethod(
                $mockEntries[0]['var'],
                $mockEntries[0]['methodArgs']
            ),
            $this->buildWithArgs($mockEntries)
        );
    }

    private function hasReturnValue(array $mockEntries): bool
    {
        foreach ($mockEntries as $mockEntry) {
            if ($mockEntry['return'] !== null) {
                return true;
            }
        }
        return false;
    }

    private function hasWithValues(array $mockEntries): bool
    {
        foreach ($mockEntries as $mockEntry) {
            if (
                count($mockEntry['with']) > 1
                || (
                    count($mockEntry['with']) === 1
                    && $mockEntry['with'][0] !== null
                )) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Arg[]
     */
    private function buildReturnArgs(array $mockEntries): array
    {
        return array_map(static function (?Expr $return) {
            return new Arg($return ?: new ConstFetch(new Name('null')));
        }, array_column($mockEntries, 'return'));
    }

    /**
     * @return Arg[]
     */
    private function buildWithArgs(array $mockEntries): array
    {
        /** @var array<int, ?Expr> $with */
        return array_map(static function (array $with) {
            return new Arg(
                new Expr\Array_(
                    array_map(static function (?Expr $expr) {
                        return new ArrayItem($expr ?: new ConstFetch(new Name('null')));
                    }, $with)
                )
            );
        }, array_column($mockEntries, 'with'));
    }

    private function fillMissingAtIndixes(array $mockEntries, Expr $var): array
    {
        $minIndex = min(array_column($mockEntries, 'index'));
        $maxIndex = max(array_column($mockEntries, 'index'));

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
            for ($i = 0; $i < $minIndex; $i++) {
                $mockEntries[] = [
                    'var' => $var,
                    'methodArgs' => [],
                    'index' => $i,
                    'return' => null,
                    'with' => [null],
                ];
            }
            $minIndex = 0;
        }

        // 0,1,2,4
        // min = 0 ; max = 4 ; count = 4
        // ADD 3
        if (($maxIndex - $minIndex + 1) !== count($mockEntries)) {
            $existingIndexes = array_column($mockEntries, 'index');
            for ($i = 0; $i < $maxIndex; $i++) {
                if (!in_array($i, $existingIndexes, true)) {
                    $mockEntries[] = [
                        'var' => $var,
                        'methodArgs' => [],
                        'index' => $i,
                        'return' => null,
                        'with' => [null],
                    ];
                }
            }
        }
        return $mockEntries;
    }
}
