<?php declare(strict_types=1);

namespace Rector\Laravel\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://medium.freecodecamp.org/moving-away-from-magic-or-why-i-dont-want-to-use-laravel-anymore-2ce098c979bd
 * @see https://laravel.com/docs/5.7/facades#facades-vs-dependency-injection
 */
final class FacadeStaticCallToConstructorInjectionRector extends AbstractRector
{
    /**
     * @see https://laravel.com/docs/5.7/facades#facades-vs-dependency-injection
     * @var string[]
     */
    private $facadeToServiceMap = [
        'Illuminate\Support\Facades\App' => 'Illuminate\Foundation\Application',
        'Illuminate\Support\Facades\Artisan' => 'Illuminate\Contracts\Console\Kernel',
        'Illuminate\Support\Facades\Auth' => 'Illuminate\Auth\AuthManager',
        'Illuminate\Support\Facades\Blade' => 'Illuminate\View\Compilers\BladeCompiler',
        'Illuminate\Support\Facades\Broadcast' => 'Illuminate\Contracts\Broadcasting\Factory',
        'Illuminate\Support\Facades\Bus' => 'Illuminate\Contracts\Bus\Dispatcher',
        'Illuminate\Support\Facades\Cache' => 'Illuminate\Cache\CacheManager',
        'Illuminate\Support\Facades\Config' => 'Illuminate\Config\Repository',
        'Illuminate\Support\Facades\Cookie' => 'Illuminate\Cookie\CookieJar',
        'Illuminate\Support\Facades\Crypt' => 'Illuminate\Encryption\Encrypter',
        'Illuminate\Support\Facades\DB' => 'Illuminate\Database\DatabaseManager',
        'Illuminate\Support\Facades\Event' => 'Illuminate\Events\Dispatcher',
        'Illuminate\Support\Facades\File' => 'Illuminate\Filesystem\Filesystem',
        'Illuminate\Support\Facades\Gate' => 'Illuminate\Contracts\Auth\Access\Gate',
        'Illuminate\Support\Facades\Hash' => 'Illuminate\Contracts\Hashing\Hasher',
        'Illuminate\Support\Facades\Lang' => 'Illuminate\Translation\Translator',
        'Illuminate\Support\Facades\Log' => 'Illuminate\Log\LogManager',
        'Illuminate\Support\Facades\Mail' => 'Illuminate\Mail\Mailer',
        'Illuminate\Support\Facades\Notification' => 'Illuminate\Notifications\ChannelManager',
        'Illuminate\Support\Facades\Password' => 'Illuminate\Auth\Passwords\PasswordBrokerManager',
        'Illuminate\Support\Facades\Queue' => 'Illuminate\Queue\QueueManager',
        'Illuminate\Support\Facades\Redirect' => 'Illuminate\Routing\Redirector',
        'Illuminate\Support\Facades\Redis' => 'Illuminate\Redis\RedisManager',
        'Illuminate\Support\Facades\Request' => 'Illuminate\Http\Request',
        'Illuminate\Support\Facades\Response' => 'Illuminate\Contracts\Routing\ResponseFactory',
        'Illuminate\Support\Facades\Route' => 'Illuminate\Routing\Router',
        'Illuminate\Support\Facades\Schema' => 'Illuminate\Database\Schema\Builder',
        'Illuminate\Support\Facades\Session' => 'Illuminate\Session\SessionManager',
        'Illuminate\Support\Facades\Storage' => 'Illuminate\Filesystem\FilesystemManager',
        'Illuminate\Support\Facades\URL' => 'Illuminate\Routing\UrlGenerator',
        'Illuminate\Support\Facades\Validator' => 'Illuminate\Validation\Factory',
        'Illuminate\Support\Facades\View' => 'Illuminate\View\Factory',
    ];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move Illuminate\Support\Facades\* static calls to constructor injection', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Illuminate\Support\Facades\Response;

class ExampleController extends Controller
{
    public function store()
    {
        return Response::view('example', ['new_example' => 123]);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Illuminate\Support\Facades\Response;

class ExampleController extends Controller
{
    /**
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    private $responseFactory;
    
    public function __construct(\Illuminate\Contracts\Routing\ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function store()
    {
        return $this->responseFactory->view('example', ['new_example' => 123]);
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
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return null;
        }

        foreach ($this->facadeToServiceMap as $facadeClass => $serviceClass) {
            if (! $this->isType($node, $facadeClass)) {
                continue;
            }

            $propertyName = $this->propertyNaming->fqnToVariableName($serviceClass);
            $this->addPropertyToClass($classNode, $serviceClass, $propertyName);

            $propertyFetchNode = $this->createPropertyFetch('this', $propertyName);
            return new MethodCall($propertyFetchNode, $node->name, $node->args);
        }

        return $node;
    }
}
