<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // EventDispatcher
        'Symfony\\Component\\HttpKernel\\Event\\FilterControllerArgumentsEvent' => 'Symfony\\Component\\HttpKernel\\Event\\ControllerArgumentsEvent',
        'Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent' => 'Symfony\\Component\\HttpKernel\\Event\\ControllerEvent',
        'Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent' => 'Symfony\\Component\\HttpKernel\\Event\\ResponseEvent',
        'Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent' => 'Symfony\\Component\\HttpKernel\\Event\\RequestEvent',
        'Symfony\\Component\\HttpKernel\\Event\\GetResponseForControllerResultEvent' => 'Symfony\\Component\\HttpKernel\\Event\\ViewEvent',
        'Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent' => 'Symfony\\Component\\HttpKernel\\Event\\ExceptionEvent',
        'Symfony\\Component\\HttpKernel\\Event\\PostResponseEvent' => 'Symfony\\Component\\HttpKernel\\Event\\TerminateEvent',
        // @todo unpack after YAML to PHP migration, Symfony\Component\HttpKernel\Client: Symfony\Component\HttpKernel\HttpKernelBrowser
        'Symfony\\Component\\HttpKernel\\EventListener\\TranslatorListener' => 'Symfony\\Component\\HttpKernel\\EventListener\\LocaleAwareListener',
    ]);
};
