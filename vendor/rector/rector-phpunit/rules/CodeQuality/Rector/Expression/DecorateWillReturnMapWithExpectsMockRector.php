<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Expression\DecorateWillReturnMapWithExpectsMockRector\DecorateWillReturnMapWithExpectsMockRectorTest
 */
final class DecorateWillReturnMapWithExpectsMockRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Decorate willReturnMap() calls with expects on the mock object if missing', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTest extends TestCase
{
    private MockObject $someMock;

    protected function setUp(): void
    {
        $this->someMock = $this->createMock(SomeClass::class);

        $this->someMock->method("someMethod")
            ->willReturnMap([
                ["arg1", "arg2", "result1"],
                ["arg3", "arg4", "result2"],
            ]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTest extends TestCase
{
    private MockObject $someMock;

    protected function setUp(): void
    {
        $this->someMock = $this->createMock(SomeClass::class);

        $this->someMock->expects($this->exactly(2))
            ->method("someMethod")
            ->willReturnMap([
                ["arg1", "arg2", "result1"],
                ["arg3", "arg4", "result2"],
            ]);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return Expression|null
     */
    public function refactor(Node $node)
    {
        if (!$node->expr instanceof MethodCall) {
            return null;
        }
        $methodCall = $node->expr;
        if (!$this->isName($methodCall->name, 'willReturnMap')) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        // allowed as can be flexible
        if ($scope->getFunctionName() === MethodName::SET_UP) {
            return null;
        }
        $topmostCall = $this->resolveTopmostCall($methodCall);
        // already covered
        if ($this->isName($topmostCall->name, 'expects')) {
            return null;
        }
        if (!$this->isObjectType($topmostCall->var, new ObjectType(PHPUnitClassName::MOCK_OBJECT))) {
            return null;
        }
        if ($methodCall->isFirstClassCallable()) {
            return null;
        }
        // count values in will map arg
        $willReturnMapArg = $methodCall->getArgs()[0] ?? null;
        if (!$willReturnMapArg instanceof Arg) {
            return null;
        }
        if (!$willReturnMapArg->value instanceof Array_) {
            return null;
        }
        $array = $willReturnMapArg->value;
        $mapCount = count($array->items);
        $topmostCall->var = new MethodCall($topmostCall->var, new Identifier('expects'), [new Arg(new MethodCall(new Variable('this'), new Identifier('exactly'), [new Arg(new Int_($mapCount))]))]);
        return $node;
    }
    private function resolveTopmostCall(MethodCall $methodCall): MethodCall
    {
        $currentCall = $methodCall;
        while ($currentCall->var instanceof MethodCall) {
            $currentCall = $currentCall->var;
        }
        return $currentCall;
    }
}
