<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\MethodName;
use Rector\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Symfony\Rector\MethodCall\MakeDispatchFirstArgumentEventRector;
use Rector\Symfony\Rector\MethodCall\WebTestCaseAssertResponseCodeRector;
# https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.3.md
return static function (RectorConfig $rectorConfig) : void {
    # https://symfony.com/blog/new-in-symfony-4-3-better-test-assertions
    $rectorConfig->rule(WebTestCaseAssertResponseCodeRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\BrowserKit\\Response', 'getStatus', 'getStatusCode'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Security\\Http\\Firewall', 'handleRequest', 'callListeners'),
        # https://github.com/symfony/http-kernel/blob/801b925e308518ddf821ba91952c41ae77c77507/Event/GetResponseForExceptionEvent.php#L55
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent', 'getException', 'getThrowable'),
        # https://github.com/symfony/http-kernel/blob/801b925e308518ddf821ba91952c41ae77c77507/Event/GetResponseForExceptionEvent.php#L67
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent', 'setException', 'setThrowable'),
    ]);
    $rectorConfig->rule(MakeDispatchFirstArgumentEventRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://symfony.com/blog/new-in-symfony-4-3-simpler-event-dispatching
        # Browser Kit
        'RectorPrefix20220607\\Symfony\\Component\\BrowserKit\\Client' => 'RectorPrefix20220607\\Symfony\\Component\\BrowserKit\\AbstractBrowser',
        # Cache
        # https://github.com/symfony/symfony/pull/29236
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Traits\\ApcuTrait\\ApcuCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Traits\\ApcuTrait\\ApcuAdapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\SimpleCacheAdapter' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\Psr16Adapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\ArrayCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\ArrayAdapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\ChainCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\ChainAdapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\DoctrineCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\DoctrineAdapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\FilesystemCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\FilesystemAdapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\MemcachedCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\MemcachedAdapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\NullCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\NullAdapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\PdoCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\PdoAdapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\PhpArrayCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\PhpArrayAdapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\PhpFilesCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\PhpFilesAdapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\RedisCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\RedisAdapter',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\TraceableCache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Adapter\\TraceableAdapterCache',
        'RectorPrefix20220607\\Symfony\\Component\\Cache\\Simple\\Psr6Cache' => 'RectorPrefix20220607\\Symfony\\Component\\Cache\\Psr16Cache',
        'RectorPrefix20220607\\Psr\\SimpleCache\\CacheInterface' => 'RectorPrefix20220607\\Symfony\\Contracts\\Cache\\CacheInterface',
        # EventDispatcher
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\FilterControllerArgumentsEvent' => 'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\ControllerArgumentsEvent',
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent' => 'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\ControllerEvent',
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent' => 'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\ResponseEvent',
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent' => 'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\RequestEvent',
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\GetResponseForControllerResultEvent' => 'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\ViewEvent',
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent' => 'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\ExceptionEvent',
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\PostResponseEvent' => 'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\TerminateEvent',
        # has lowest priority, have to be last
        'RectorPrefix20220607\\Symfony\\Component\\EventDispatcher\\Event' => 'RectorPrefix20220607\\Symfony\\Contracts\\EventDispatcher\\Event',
        # MimeType
        'RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\File\\MimeType\\MimeTypeGuesserInterface' => 'RectorPrefix20220607\\Symfony\\Component\\Mime\\MimeTypesInterface',
        'RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\File\\MimeType\\ExtensionGuesserInterface' => 'RectorPrefix20220607\\Symfony\\Component\\Mime\\MimeTypesInterface',
        'RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\File\\MimeType\\MimeTypeExtensionGuesser' => 'RectorPrefix20220607\\Symfony\\Component\\Mime\\MimeTypes',
        'RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\File\\MimeType\\FileBinaryMimeTypeGuesser' => 'RectorPrefix20220607\\Symfony\\Component\\Mime\\FileBinaryMimeTypeGuesser',
        'RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\File\\MimeType\\FileinfoMimeTypeGuesser' => 'RectorPrefix20220607\\Symfony\\Component\\Mime\\FileinfoMimeTypeGuesser',
        # HttpKernel
        # @todo unpack after YAML to PHP migration, Symfony\Component\HttpKernel\Client: Symfony\Component\HttpKernel\HttpKernelBrowser
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\EventListener\\TranslatorListener' => 'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\EventListener\\LocaleAwareListener',
        # Security
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\Argon2iPasswordEncoder' => 'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\SodiumPasswordEncoder',
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\BCryptPasswordEncoder' => 'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\NativePasswordEncoder',
    ]);
    # https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.3.md#workflow
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('RectorPrefix20220607\\Symfony\\Component\\Workflow\\MarkingStore\\MarkingStoreInterface', 'setMarking', 2, 'context', [])]);
    $rectorConfig->ruleWithConfiguration(AddMethodParentCallRector::class, ['RectorPrefix20220607\\Symfony\\Component\\EventDispatcher\\EventDispatcher' => MethodName::CONSTRUCT]);
};
