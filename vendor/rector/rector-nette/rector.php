<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->importNames();
    $rectorConfig->parallel();
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);
    $rectorConfig->skip([
        // for tests
        '*/Source/*',
        '*/Fixture/*',
    ]);
    $rectorConfig->ruleWithConfiguration(StringClassNameToClassConstantRector::class, ['Nette\\*', 'Symfony\\Component\\Translation\\TranslatorInterface', 'Symfony\\Contracts\\EventDispatcher\\Event', 'Kdyby\\Events\\Subscriber']);
    // needed for DEAD_CODE list, just in split package like this
    $rectorConfig->sets([__DIR__ . '/config/config.php', LevelSetList::UP_TO_PHP_81, SetList::DEAD_CODE, SetList::CODE_QUALITY]);
};
