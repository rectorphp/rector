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
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector\AssertTrueFalseToSpecificMethodRectorTest
 */
final class MigrateAtToWithConsecutiveAndWillReturnOnConsecutiveCallsRector extends AbstractRector
{
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
                    $willReturnValue = new ConstFetch(new Name('null'));
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
                    $withArgs = [new ConstFetch(new Name('null'))];
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
            usort($mockEntries, static function ($mockEntryA, $mockEntryB) {
                return $mockEntryA['index'] > $mockEntryB['index'];
            });

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
                        $stmt->expr = new MethodCall(
                            new MethodCall(
                                new MethodCall(
                                    $mockEntries[0]['var'],
                                    new Name('method'),
                                    $mockEntries[0]['methodArgs']
                                ),
                                new Name('withConsecutive'),
                                array_map(static function (array $mockEntry) {
                                    return new Arg(
                                        new Expr\Array_(array_map(static function (Expr $expr) {
                                            return new ArrayItem($expr);
                                        }, $mockEntry['with']))
                                    );
                                }, $mockEntries)
                            ),
                            new Name('willReturnOnConsecutiveCalls'),
                            array_map(static function (array $mockEntry) {
                                return new Arg($mockEntry['return']);
                            }, $mockEntries)
                        );
                    }
                }
            }
        }

        return $node;
    }
}
