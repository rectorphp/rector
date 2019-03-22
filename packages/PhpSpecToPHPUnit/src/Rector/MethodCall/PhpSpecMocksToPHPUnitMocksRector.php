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
use Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector;

final class PhpSpecMocksToPHPUnitMocksRector extends AbstractPhpSpecToPHPUnitRector
{
    /**
     * @var string[]
     */
    private $mockVariableNames = [];

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
        if (! $this->isInPhpSpecBehavior($node)) {
            return null;
        }

        if ($node instanceof ClassMethod) {
            // public = tests, protected = internal, private = own (no framework magic)
            if ($node->isPrivate()) {
                return null;
            }

            $this->processMethodParamsToMocks($node);

            return $node;
        }

        return $this->processMethodCall($node);
    }

    protected function reset(): void
    {
        // reset once per class
        $this->mockVariableNames = [];
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
            if (! $methodCall->var instanceof MethodCall) {
                throw new ShouldNotHappenException();
            }

            $mockMethodName = $this->getName($methodCall->var);
            if ($mockMethodName === null) {
                throw new ShouldNotHappenException();
            }

            $methodCall->var->name = new Identifier('expects');
            $thisOnceMethodCall = new MethodCall(new Variable('this'), new Identifier('once'));
            $methodCall->var->args = [new Arg($thisOnceMethodCall)];

            $methodCall->name = new Identifier('method');
            $methodCall->args = [new Arg(new String_($mockMethodName))];

            return $methodCall;
        }

        return null;
    }
}
