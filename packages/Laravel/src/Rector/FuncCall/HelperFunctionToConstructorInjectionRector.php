<?php declare(strict_types=1);

namespace Rector\Laravel\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/laravel/framework/blob/78828bc779e410e03cc6465f002b834eadf160d2/src/Illuminate/Foundation/helpers.php#L959
 * @see https://gist.github.com/barryvdh/bb6ffc5d11e0a75dba67
 */
final class HelperFunctionToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $functionToService = [
        // set/get
        'config' => [
            'type' => 'Illuminate\Contracts\Config\Repository',
            'array_method' => 'set',
            'non_array_method' => 'get',
        ],
        'session' => [
            'type' => 'Illuminate\Session\SessionManager',
            'property' => 'sessionManager',
            'array_method' => 'put',
            'non_array_method' => 'get',
        ],

        // methods if args/property fetch
        'policy' => [
            'type' => 'Illuminate\Contracts\Auth\Access\Gate',
            'property' => 'policy',
            'method_if_args' => 'getPolicyFor',
        ],
        'cookie' => [
            'type' => 'Illuminate\Contracts\Cookie\Factory',
            'property' => 'cookieFactory',
            'method_if_args' => 'make',
        ],
        // router
        'put' => [
            'type' => 'Illuminate\Routing\Router',
            'property' => 'router',
            'method_if_args' => 'put',
        ],
        'get' => [
            'type' => 'Illuminate\Routing\Router',
            'property' => 'router',
            'method_if_args' => 'get',
        ],
        'post' => [
            'type' => 'Illuminate\Routing\Router',
            'property' => 'router',
            'method_if_args' => 'post',
        ],
        'patch' => [
            'type' => 'Illuminate\Routing\Router',
            'property' => 'router',
            'method_if_args' => 'patch',
        ],
        'delete' => [
            'type' => 'Illuminate\Routing\Router',
            'property' => 'router',
            'method_if_args' => 'delete',
        ],
        'resource' => [
            'type' => 'Illuminate\Routing\Router',
            'property' => 'router',
            'method_if_args' => 'resource',
        ],
        'response' => [
            'type' => 'Illuminate\Contracts\Routing\ResponseFactory',
            'property' => 'responseFactory',
            'method_if_args' => 'make',
        ],

        'info' => [
            'type' => 'Illuminate\Log\Writer',
            'property' => 'logWriter',
            'method_if_args' => 'info',
        ],
        'view' => [
            'type' => 'Illuminate\Contracts\View\Factory',
            'property' => 'viewFactory',
            'method_if_args' => 'make',
        ],
        'bcrypt' => [
            'type' => 'Illuminate\Hashing\BcryptHasher',
            'property' => 'bcryptHasher',
            'method_if_args' => 'make',
        ],
        'redirect' => [
            'type' => 'Illuminate\Routing\Redirector',
            'property' => 'redirector',
            'method_if_args' => 'back',
        ],
        'back' => [
            'type' => 'Illuminate\Routing\Redirector',
            'property' => 'redirector',
            'method_if_args' => 'back',
        ],
        'broadcast' => [
            'type' => 'Illuminate\Contracts\Broadcasting\Factory',
            'property' => 'broadcastFactory',
            'method_if_args' => 'event',
        ],
        'event' => [
            'type' => 'Illuminate\Events\Dispatcher',
            'property' => 'eventDispatcher',
            'method_if_args' => 'fire',
        ],
        'dispatch' => [
            'type' => 'Illuminate\Events\Dispatcher',
            'property' => 'eventDispatcher',
            'method_if_args' => 'dispatch',
        ],
        'route' => [
            'type' => 'Illuminate\Routing\UrlGenerator',
            'property' => 'urlGenerator',
            'method_if_args' => 'route',
        ],
        'auth' => [
            'type' => 'Illuminate\Contracts\Auth\Guard',
            'property' => 'guard',
        ],
        'asset' => [
            'type' => 'Illuminate\Routing\UrlGenerator',
            'property' => 'urlGenerator',
            'method_if_args' => 'asset',
        ],
        'url' => [
            'type' => 'Illuminate\Contracts\Routing\UrlGenerator',
            'property' => 'urlGenerator',
            'method_if_args' => 'to',
        ],
        'action' => [
            'type' => 'Illuminate\Routing\UrlGenerator',
            'property' => 'urlGenerator',
            'method_if_args' => 'action',
        ],
        'trans' => [
            'type' => 'Illuminate\Translation\Translator',
            'property' => 'translator',
            'method_if_args' => 'trans',
        ],
        'trans_choice' => [
            'type' => 'Illuminate\Translation\Translator',
            'property' => 'translator',
            'method_if_args' => 'transChoice',
        ],
        'logger' => [
            'type' => 'Illuminate\Log\Writer',
            'property' => 'logWriter',
            'method_if_args' => 'debug',
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
        $viewFactory = view();
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

            if (count($node->args) === 0) {
                return $propertyFetchNode;
            }

            if (isset($service['method_if_args']) && count($node->args) >= 1) {
                return new MethodCall($propertyFetchNode, $service['method_if_args'], $node->args);
            }

            if (isset($service['array_method']) && $this->isArrayType($node->args[0]->value)) {
                return new MethodCall($propertyFetchNode, $service['array_method'], $node->args);
            }

            if (isset($service['non_array_method']) && ! $this->isArrayType($node->args[0]->value)) {
                return new MethodCall($propertyFetchNode, $service['non_array_method'], $node->args);
            }

            throw new ShouldNotHappenException();
        }

        return null;
    }
}
