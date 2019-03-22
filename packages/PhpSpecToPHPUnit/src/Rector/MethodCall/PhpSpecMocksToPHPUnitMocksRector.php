<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\MethodCall;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class PhpSpecMocksToPHPUnitMocksRector extends AbstractRector
{
    /**
     * @var string
     */
    private $objectBehaviorClass;

    /**
     * @var string[]
     */
    private $mockVariableNames = [];

    public function __construct(string $objectBehaviorClass = 'PhpSpec\ObjectBehavior')
    {
        $this->objectBehaviorClass = $objectBehaviorClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate PhpSpec mocking behavior to PHPUnit mocks', [
            new CodeSample(
                <<<'CODE_SAMPLE'
namespace spec\SomeNamespaceForThisTest;

use PhpSpec\ObjectBehavior;

class OrderSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
namespace spec\SomeNamespaceForThisTest;

class OrderSpec extends ObjectBehavior
{
    /**
     * @var \SomeNamespaceForThisTest\Order
     */
    private $order;
    protected function setUp()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);

        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);

        $factory->method('createShippingMethodFor')->expects($this->once())->willReturn($shippingMethod);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, MethodCall::class];
    }

    /**
     * @param ClassMethod|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            return null;
        }

        if (! $this->isType($classNode, $this->objectBehaviorClass)) {
            return null;
        }

        if ($node instanceof ClassMethod) {
            // only public methods are tests
            if (! $node->isPublic()) {
                return null;
            }

            $this->processMethodParamsToMocks($node);

            return $node;
        }

        return $this->processMethodCall($node);
    }

    private function createCreateMockCall(Param $param, Name $name): Expression
    {
        $methodCall = new MethodCall(new Variable('this'), 'createMock');
        $methodCall->args[] = new Arg(new ClassConstFetch($name, 'class'));

        $assign = new Assign($param->var, $methodCall);
        $assignExpression = new Expression($assign);

        // add @var doc comment
        $varDoc = $this->createMockVarDoc($param, $name);
        $assignExpression->setDocComment(new Doc($varDoc));

        return $assignExpression;
    }

    private function createMockVarDoc(Param $param, Name $name): string
    {
        $paramType = (string) ($name->getAttribute('originalName') ?: $name);
        $variableName = $this->getName($param->var);

        if ($variableName === null) {
            throw new ShouldNotHappenException();
        }

        $this->mockVariableNames[] = $variableName;

        return sprintf(
            '/** @var %s|\%s $%s */',
            $paramType,
            'PHPUnit\Framework\MockObject\MockObject',
            $variableName
        );
    }

    private function processMethodParamsToMocks(ClassMethod $classMethod): void
    {
        // remove params and turn them to instances
        $assigns = [];
        foreach ((array) $classMethod->params as $param) {
            if (! $param->type instanceof Name) {
                throw new ShouldNotHappenException();
            }

            $assigns[] = $this->createCreateMockCall($param, $param->type);
        }

        // remove all params
        $classMethod->params = [];

        $classMethod->stmts = array_merge($assigns, (array) $classMethod->stmts);
    }

    private function processMethodCall(MethodCall $methodCall): ?MethodCall
    {
        if ($this->isName($methodCall, 'shouldBeCalled')) {
            $methodCall->name = new Identifier('expects');
            $thisOnceMethodCall = new MethodCall(new Variable('this'), new Identifier('once'));
            $methodCall->args[] = new Arg($thisOnceMethodCall);

            return $methodCall;
        }

        // add method mocks on mock variable
        if ($this->isNames($methodCall->var, $this->mockVariableNames)) {
            $nextNode = $methodCall->getAttribute(Attribute::NEXT_NODE);

            // @todo check maybe for the name "isName($node, 'shouldBeCalled')"
            if ($nextNode instanceof Identifier && $this->isName($nextNode, 'shouldBeCalled')) {
                /** @var string $methodName */
                $methodName = $this->getName($methodCall);

                $methodCall->args = [new Arg(new String_($methodName))];
                $methodCall->name = new Identifier('method');

                return $methodCall;
            }
        }

        return null;
    }
}
