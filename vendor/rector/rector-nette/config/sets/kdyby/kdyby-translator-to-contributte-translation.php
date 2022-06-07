<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Nette\Kdyby\Rector\MethodCall\WrapTransParameterNameRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Kdyby\\Translation\\Translator', 'translate', 'trans')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['RectorPrefix20220607\\Kdyby\\Translation\\Translator' => 'RectorPrefix20220607\\Nette\\Localization\\ITranslator', 'RectorPrefix20220607\\Kdyby\\Translation\\DI\\ITranslationProvider' => 'RectorPrefix20220607\\Contributte\\Translation\\DI\\TranslationProviderInterface', 'RectorPrefix20220607\\Kdyby\\Translation\\Phrase' => 'RectorPrefix20220607\\Contributte\\Translation\\Wrappers\\Message']);
    $rectorConfig->rule(WrapTransParameterNameRector::class);
};
