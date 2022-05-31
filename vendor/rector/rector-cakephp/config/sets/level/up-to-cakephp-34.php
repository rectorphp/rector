<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\CakePHP\Set\CakePHPSetList;
use Rector\Config\RectorConfig;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([\Rector\CakePHP\Set\CakePHPSetList::CAKEPHP_30, \Rector\CakePHP\Set\CakePHPSetList::CAKEPHP_34]);
};
