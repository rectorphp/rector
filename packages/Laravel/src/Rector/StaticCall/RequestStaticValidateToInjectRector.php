<?php declare(strict_types=1);

namespace Rector\Laravel\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Manipulator\ClassMethodManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/laravel/framework/pull/27276
 */
final class RequestStaticValidateToInjectRector extends AbstractRector
{
    /**
     * @var ClassMethodManipulator
     */
    private $classMethodManipulator;

    /**
     * @var string
     */
    private $requestClass = 'Illuminate\Http\Request';

    public function __construct(ClassMethodManipulator $classMethodManipulator)
    {
        $this->classMethodManipulator = $classMethodManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change static validate() method to $request->validate()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Illuminate\Http\Request;

class SomeClass
{
    public function store()
    {
        $validatedData = Request::validate(['some_attribute' => 'required']);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Illuminate\Http\Request;

class SomeClass
{
    public function store(\Illuminate\Http\Request $request)
    {
        $validatedData = $request->validate(['some_attribute' => 'required']);
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
        return [StaticCall::class, FuncCall::class];
    }

    /**
     * @param StaticCall|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $requestName = $this->classMethodManipulator->addMethodParameterIfMissing(
            $node,
            $this->requestClass,
            ['request', 'httpRequest']
        );

        $variable = new Variable($requestName);

        if ($node instanceof FuncCall) {
            return $variable;
        }

        return new MethodCall($variable, $node->name, $node->args);
    }

    /**
     * @param StaticCall|FuncCall $node
     */
    private function shouldSkip(Node $node): bool
    {
        if ($node instanceof StaticCall) {
            return ! $this->isType($node, $this->requestClass);
        }

        if ($node instanceof FuncCall) {
            $class = $node->getAttribute(Attribute::CLASS_NODE);
            if (! $class instanceof Class_) {
                return true;
            }

            return ! $this->isName($node, 'request');
        }

        return true;
    }
}
