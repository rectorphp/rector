<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\CakePHP\Set\CakePHPSetList;
use Rector\Config\RectorConfig;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(\Rector\CakePHP\Set\CakePHPSetList::CAKEPHP_30);
    $rectorConfig->import(\Rector\CakePHP\Set\CakePHPSetList::CAKEPHP_34);
};
