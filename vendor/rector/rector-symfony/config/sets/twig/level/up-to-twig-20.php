<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\TwigLevelSetList;
use Rector\Symfony\Set\TwigSetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([TwigSetList::TWIG_20, TwigLevelSetList::UP_TO_TWIG_140]);
};
