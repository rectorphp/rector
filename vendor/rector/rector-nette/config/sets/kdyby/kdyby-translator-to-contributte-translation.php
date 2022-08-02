<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Nette\Kdyby\Rector\MethodCall\WrapTransParameterNameRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Kdyby\\Translation\\Translator', 'translate', 'trans')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Kdyby\\Translation\\Translator' => 'Nette\\Localization\\ITranslator', 'Kdyby\\Translation\\DI\\ITranslationProvider' => 'Contributte\\Translation\\DI\\TranslationProviderInterface', 'Kdyby\\Translation\\Phrase' => 'Contributte\\Translation\\Wrappers\\Message']);
    $rectorConfig->rule(WrapTransParameterNameRector::class);
};
