<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\PHPUnit\ValueObject\ExpectationMock;
use Rector\PHPUnit\ValueObject\ExpectationMockCollection;
final class ConsecutiveAssertionFactory
{
    /**
     * @var array<string, string>
     */
    private const REPLACE_WILL_MAP = ['willReturnMap' => 'returnValueMap', 'willReturnArgument' => 'returnArgument', 'willReturnCallback' => 'returnCallback', 'willThrowException' => 'throwException'];
    public function createAssertionFromExpectationMockCollection(\Rector\PHPUnit\ValueObject\ExpectationMockCollection $expectationMockCollection) : \PhpParser\Node\Expr\MethodCall
    {
        $expectationMocks = $expectationMockCollection->getExpectationMocks();
        $variable = $expectationMocks[0]->getExpectationVariable();
        $methodArguments = $expectationMocks[0]->getMethodArguments();
        $expectationMocks = $this->sortExpectationMocksByIndex($expectationMocks);
        if (!$expectationMockCollection->hasReturnValues()) {
            return $this->createWithConsecutive($this->createMethod($variable, $methodArguments), $this->createWithArgs($expectationMocks));
        }
        if ($expectationMockCollection->hasWithValues()) {
            return $this->createWillReturnOnConsecutiveCalls($this->createWithConsecutive($this->createMethod($variable, $methodArguments), $this->createWithArgs($expectationMocks)), $this->createReturnArgs($expectationMocks));
        }
        return $this->createWillReturnOnConsecutiveCalls($this->createMethod($variable, $methodArguments), $this->createReturnArgs($expectationMocks));
    }
    /**
     * @param Arg[] $args
     */
    public function createWillReturnOnConsecutiveCalls(\PhpParser\Node\Expr $expr, array $args) : \PhpParser\Node\Expr\MethodCall
    {
        return $this->createMethodCall($expr, 'willReturnOnConsecutiveCalls', $args);
    }
    /**
     * @param Arg[] $args
     */
    public function createMethod(\PhpParser\Node\Expr $expr, array $args) : \PhpParser\Node\Expr\MethodCall
    {
        return $this->createMethodCall($expr, 'method', $args);
    }
    /**
     * @param Arg[] $args
     */
    public function createWithConsecutive(\PhpParser\Node\Expr $expr, array $args) : \PhpParser\Node\Expr\MethodCall
    {
        return $this->createMethodCall($expr, 'withConsecutive', $args);
    }
    public function createWillReturn(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr
    {
        if (!$methodCall->name instanceof \PhpParser\Node\Identifier) {
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
        if (\array_key_exists($methodCallName, self::REPLACE_WILL_MAP)) {
            return $this->createMappedWillReturn($methodCallName, $methodCall);
        }
        return $methodCall->args[0]->value;
    }
    /**
     * @param ExpectationMock[] $expectationMocks
     * @return Arg[]
     */
    private function createReturnArgs(array $expectationMocks) : array
    {
        return \array_map(static function (\Rector\PHPUnit\ValueObject\ExpectationMock $expectationMock) : Arg {
            return new \PhpParser\Node\Arg($expectationMock->getReturn() instanceof \PhpParser\Node\Expr ? $expectationMock->getReturn() : new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null')));
        }, $expectationMocks);
    }
    /**
     * @param ExpectationMock[] $expectationMocks
     * @return Arg[]
     */
    private function createWithArgs(array $expectationMocks) : array
    {
        return \array_map(static function (\Rector\PHPUnit\ValueObject\ExpectationMock $expectationMock) : Arg {
            $arrayItems = \array_map(static function (?\PhpParser\Node\Expr $expr) : ArrayItem {
                return new \PhpParser\Node\Expr\ArrayItem($expr instanceof \PhpParser\Node\Expr ? $expr : new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null')));
            }, $expectationMock->getWithArguments());
            return new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\Array_($arrayItems));
        }, $expectationMocks);
    }
    private function createWillReturnSelf() : \PhpParser\Node\Expr\MethodCall
    {
        return $this->createMethodCall(new \PhpParser\Node\Expr\Variable('this'), 'returnSelf', []);
    }
    private function createWillReturnReference(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr\New_
    {
        return new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('PHPUnit\\Framework\\MockObject\\Stub\\ReturnReference'), [new \PhpParser\Node\Arg($methodCall->args[0]->value)]);
    }
    private function createMappedWillReturn(string $methodCallName, \PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr\MethodCall
    {
        return $this->createMethodCall(new \PhpParser\Node\Expr\Variable('this'), self::REPLACE_WILL_MAP[$methodCallName], [new \PhpParser\Node\Arg($methodCall->args[0]->value)]);
    }
    /**
     * @param Arg[] $args
     */
    private function createMethodCall(\PhpParser\Node\Expr $expr, string $name, array $args) : \PhpParser\Node\Expr\MethodCall
    {
        return new \PhpParser\Node\Expr\MethodCall($expr, new \PhpParser\Node\Identifier($name), $args);
    }
    /**
     * @param ExpectationMock[] $expectationMocks
     * @return ExpectationMock[]
     */
    private function sortExpectationMocksByIndex(array $expectationMocks) : array
    {
        \usort($expectationMocks, static function (\Rector\PHPUnit\ValueObject\ExpectationMock $expectationMockA, \Rector\PHPUnit\ValueObject\ExpectationMock $expectationMockB) : int {
            return $expectationMockA->getIndex() > $expectationMockB->getIndex() ? 1 : -1;
        });
        return $expectationMocks;
    }
}
