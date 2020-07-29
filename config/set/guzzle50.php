<?php

declare(strict_types=1);

use GuzzleHttp\Cookie\SetCookie;
use Rector\Generic\Rector\Function_\FunctionToMethodCallRector;
use Rector\Generic\Rector\StaticCall\StaticCallToFunctionRector;
use Rector\Guzzle\Rector\MethodCall\MessageAsArrayRector;
use Rector\MagicDisclosure\Rector\ClassMethod\ReturnThisRemoveRector;
use Rector\MagicDisclosure\Rector\MethodCall\DefluentMethodCallRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        'classes_to_defluent',
        ['GuzzleHttp\Collection', 'GuzzleHttp\Url', 'GuzzleHttp\Query', 'GuzzleHttp\Post\PostBody', SetCookie::class]
    );

    $services = $containerConfigurator->services();

    # both uses "%classes_to_defluent%
    #diff-810cdcfdd8a6b9e1fc0d1e96d7786874
    # covers https://github.com/guzzle/guzzle/commit/668209c895049759377593eed129e0949d9565b7
    $services->set(ReturnThisRemoveRector::class)
        ->arg('$classesToDefluent', '%classes_to_defluent%');

    $services->set(DefluentMethodCallRector::class)
        ->arg('$namesToDefluent', '%classes_to_defluent%');

    $services->set(FunctionToMethodCallRector::class)
        ->arg('$functionToMethodCall', [
            'GuzzleHttp\json_decode' => ['GuzzleHttp\Utils', 'jsonDecode'],
            'GuzzleHttp\get_path' => ['GuzzleHttp\Utils', 'getPath'],
        ]);

    $services->set(StaticCallToFunctionRector::class)
        ->arg('$staticCallToFunctionByType', [
            'GuzzleHttp\Utils' => [
                'setPath' => 'GuzzleHttp\set_path',
            ],
            'GuzzleHttp\Pool' => [
                'batch' => 'GuzzleHttp\Pool\batch',
            ],
        ]);

    $services->set(MessageAsArrayRector::class);

    $services->set(RenameMethodRector::class)
        ->arg('$oldToNewMethodsByClass', [
            'GuzzleHttp\Message\MessageInterface' => [
                'getHeaderLines' => 'getHeaderAsArray',
            ],
        ]);
};
