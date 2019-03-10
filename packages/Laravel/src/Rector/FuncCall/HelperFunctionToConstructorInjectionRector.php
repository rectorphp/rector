<?php declare(strict_types=1);

namespace Rector\Laravel\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/laravel/framework/blob/78828bc779e410e03cc6465f002b834eadf160d2/src/Illuminate/Foundation/helpers.php#L959
 */
final class HelperFunctionToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $functionToService = [
        'view' => [
            'type' => 'Illuminate\Contracts\View\Factory',
            'property' => 'viewFactory',
            'method_if_args' => 'make',
        ],
        'broadcast' => [
            'type' => 'Illuminate\Contracts\Broadcasting\Factory',
            'property' => 'broadcastFactory',
            'method_if_args' => 'event',
        ],
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move help facade-like function calls to constructor injection', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeController
{
    public function action()
    {
        $template = view('template.blade');
        $viewFatory = view();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeController
{
    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $viewFactory;

    public function __construct(\Illuminate\Contracts\View\Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function action()
    {
        $template = $this->viewFactory->make('template.blade');
        $viewFactory = $this->viewFactory;
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // we can inject only in class context
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return null;
        }

        foreach ($this->functionToService as $function => $service) {
            if (! $this->isName($node, $function)) {
                continue;
            }

            $this->addPropertyToClass($classNode, $service['type'], $service['property']);
            $propertyFetchNode = $this->createPropertyFetch('this', $service['property']);

            if (isset($service['method_if_args'])) {
                // related only to view
                if (count($node->args) >= 1) {
                    return new MethodCall($propertyFetchNode, $service['method_if_args'], $node->args);
                }
            }

            return $propertyFetchNode;
        }

        return null;
    }
}
