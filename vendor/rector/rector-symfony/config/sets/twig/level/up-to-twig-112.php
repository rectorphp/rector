<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\TwigSetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([TwigSetList::TWIG_112]);
};
