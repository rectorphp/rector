<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\StaticCall\StaticCallToNewRector;
use Rector\Transform\ValueObject\StaticCallToNew;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(StaticCallToNewRector::class, [new StaticCallToNew('Symfony\\Component\\HttpFoundation\\Response', 'create'), new StaticCallToNew('Symfony\\Component\\HttpFoundation\\JsonResponse', 'create'), new StaticCallToNew('Symfony\\Component\\HttpFoundation\\RedirectResponse', 'create'), new StaticCallToNew('Symfony\\Component\\HttpFoundation\\StreamedResponse', 'create')]);
};
