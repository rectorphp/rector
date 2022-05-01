<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Nette\Kdyby\Rector\MethodCall\WrapTransParameterNameRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('Kdyby\\Translation\\Translator', 'translate', 'trans')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Kdyby\\Translation\\Translator' => 'Nette\\Localization\\ITranslator', 'Kdyby\\Translation\\DI\\ITranslationProvider' => 'Contributte\\Translation\\DI\\TranslationProviderInterface', 'Kdyby\\Translation\\Phrase' => 'Contributte\\Translation\\Wrappers\\Message']);
    $rectorConfig->rule(\Rector\Nette\Kdyby\Rector\MethodCall\WrapTransParameterNameRector::class);
};
