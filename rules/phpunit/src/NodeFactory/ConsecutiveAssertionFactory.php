<?php
declare(strict_types=1);


namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\PHPUnit\ValueObject\ExpectationMock;
use Rector\PHPUnit\ValueObject\ExpectationMockCollection;

final class ConsecutiveAssertionFactory
{
    private const REPLACE_WILL_MAP = [
        'willReturnMap' => 'returnValueMap',
        'willReturnArgument' => 'returnArgument',
        'willReturnCallback' => 'returnCallback',
        'willThrowException' => 'throwException',
    ];

    public function createAssertionFromExpectationMockCollection(ExpectationMockCollection $expectationMockCollection): MethodCall
    {
        $expectationMocks = $expectationMockCollection->getExpectationMocks();

        $var = $expectationMocks[0]->getExpectationVariable();
        $methodArguments = $expectationMocks[0]->getMethodArguments();

        usort($expectationMocks, static function (ExpectationMock $expectationMockA, ExpectationMock $expectationMockB) {
            return $expectationMockA->getIndex() > $expectationMockB->getIndex() ? 1 : -1;
        });

        if (!$this->hasReturnValue($expectationMocks)) {
            return $this->createWithConsecutive(
                $this->createMethod(
                    $var,
                    $methodArguments
                ),
                $this->createWithArgs($expectationMocks)
            );
        }

        if ($this->hasWithValues($expectationMocks)) {
            return $this->createWillReturnOnConsecutiveCalls(
                $this->createWithConsecutive(
                    $this->createMethod(
                        $var,
                        $methodArguments
                    ),
                    $this->createWithArgs($expectationMocks)
                ),
                $this->createReturnArgs($expectationMocks)
            );
        }

        return $this->createWillReturnOnConsecutiveCalls(
            $this->createMethod(
                $var,
                $methodArguments
            ),
            $this->createReturnArgs($expectationMocks)
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
    private function createReturnArgs(array $expectationMocks): array
    {
        return array_map(static function (ExpectationMock $expectationMock) {
            return new Arg($expectationMock->getReturn() ?: new ConstFetch(new Name('null')));
        }, $expectationMocks);
    }

    /**
     * @param ExpectationMock[] $expectationMocks
     * @return Arg[]
     */
    private function createWithArgs(array $expectationMocks): array
    {
        return array_map(static function (ExpectationMock $expectationMock) {
            $arrayItems = array_map(static function (?Expr $expr) {
                return new ArrayItem($expr ?: new ConstFetch(new Name('null')));
            }, $expectationMock->getWithArguments());
            return new Arg(
                new Expr\Array_(
                    $arrayItems
                )
            );
        }, $expectationMocks);
    }

    /**
     * @param Arg[] $args
     */
    public function createWillReturnOnConsecutiveCalls(Expr $expr, array $args): MethodCall
    {
        return $this->createMethodCall($expr, 'willReturnOnConsecutiveCalls', $args);
    }

    /**
     * @param Arg[] $args
     */
    public function createMethod(Expr $expr, array $args): MethodCall
    {
        return $this->createMethodCall($expr, 'method', $args);
    }

    /**
     * @param Arg[] $args
     */
    public function createWithConsecutive(Expr $expr, array $args): MethodCall
    {
        return $this->createMethodCall($expr, 'withConsecutive', $args);
    }

    public function createWillReturn(MethodCall $methodCall): Expr
    {
        if (!$methodCall->name instanceof Identifier) {
            return $methodCall;
        }

        $methodCallName = $methodCall->name->name;
        if ($methodCallName === 'will') {
            return $methodCall->args[0]->value;
        }

        if ($methodCallName === 'willReturnSelf') {
            return $this->createWillReturnSelf();
        }

        if ($methodCallName === 'willReturnReference') {
            return $this->createWillReturnReference($methodCall);
        }

        if (array_key_exists($methodCallName, self::REPLACE_WILL_MAP)) {
            return $this->createMappedWillReturn($methodCallName, $methodCall);
        }

        return $methodCall->args[0]->value;
    }

    private function createWillReturnSelf(): MethodCall
    {
        return $this->createMethodCall(
            new Variable('this'),
            'returnSelf',
            []
        );
    }

    private function createWillReturnReference(MethodCall $methodCall): Expr\New_
    {
        return new Expr\New_(
            new FullyQualified('PHPUnit\Framework\MockObject\Stub\ReturnReference'),
            [new Arg($methodCall->args[0]->value)]
        );
    }

    private function createMappedWillReturn(string $methodCallName, MethodCall $methodCall): MethodCall
    {
        return $this->createMethodCall(
            new Variable('this'),
            self::REPLACE_WILL_MAP[$methodCallName],
            [new Arg($methodCall->args[0]->value)]
        );
    }

    /**
     * @param Arg[] $args
     */
    private function createMethodCall(Expr $expr, string $name, array $args): MethodCall
    {
        return new MethodCall(
            $expr,
            new Identifier($name),
            $args
        );
    }
}
