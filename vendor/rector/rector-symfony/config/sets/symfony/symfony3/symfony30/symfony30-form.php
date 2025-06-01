<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Symfony\Symfony30\Rector\ClassMethod\FormTypeGetParentRector;
use Rector\Symfony\Symfony30\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\FormTypeInstanceToClassConstRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\OptionNameRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\ReadOnlyOptionToAttributeRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\StringFormTypeToClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([FormTypeInstanceToClassConstRector::class, StringFormTypeToClassRector::class, RemoveDefaultGetBlockPrefixRector::class, FormTypeGetParentRector::class, OptionNameRector::class, ReadOnlyOptionToAttributeRector::class, ChangeStringCollectionOptionToConstantRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassConstFetch('Symfony\\Component\\Form\\FormEvents', 'PRE_BIND', 'PRE_SUBMIT'), new RenameClassConstFetch('Symfony\\Component\\Form\\FormEvents', 'BIND', 'SUBMIT'), new RenameClassConstFetch('Symfony\\Component\\Form\\FormEvents', 'POST_BIND', 'POST_SUBMIT'), new RenameClassConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFEVEN', 'ROUND_HALF_EVEN'), new RenameClassConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFUP', 'ROUND_HALF_UP'), new RenameClassConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFDOWN', 'ROUND_HALF_DOWN')]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\\Component\\Form\\AbstractType', 'getName', 'getBlockPrefix'), new MethodCallRename('Symfony\\Component\\Form\\FormTypeInterface', 'getName', 'getBlockPrefix'), new MethodCallRename('Symfony\\Component\\Form\\FormTypeInterface', 'setDefaultOptions', 'configureOptions'), new MethodCallRename('Symfony\\Component\\Form\\ResolvedFormTypeInterface', 'getName', 'getBlockPrefix'), new MethodCallRename('Symfony\\Component\\Form\\AbstractTypeExtension', 'setDefaultOptions', 'configureOptions'), new MethodCallRename('Symfony\\Component\\Form\\Form', 'bind', 'submit'), new MethodCallRename('Symfony\\Component\\Form\\Form', 'isBound', 'isSubmitted')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\\Component\\Form\\Util\\VirtualFormAwareIterator' => 'Symfony\\Component\\Form\\Util\\InheritDataAwareIterator', 'Symfony\\Component\\Form\\Tests\\Extension\\Core\\Type\\TypeTestCase' => 'Symfony\\Component\\Form\\Test\\TypeTestCase', 'Symfony\\Component\\Form\\Tests\\FormIntegrationTestCase' => 'Symfony\\Component\\Form\\Test\\FormIntegrationTestCase', 'Symfony\\Component\\Form\\Tests\\FormPerformanceTestCase' => 'Symfony\\Component\\Form\\Test\\FormPerformanceTestCase', 'Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\ChoiceListInterface' => 'Symfony\\Component\\Form\\ChoiceList\\ChoiceListInterface', 'Symfony\\Component\\Form\\Extension\\Core\\View\\ChoiceView' => 'Symfony\\Component\\Form\\ChoiceList\\View\\ChoiceView', 'Symfony\\Component\\Form\\Extension\\Csrf\\CsrfProvider\\CsrfProviderInterface' => 'Symfony\\Component\\Security\\Csrf\\CsrfTokenManagerInterface', 'Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\ChoiceList' => 'Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList', 'Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\LazyChoiceList' => 'Symfony\\Component\\Form\\ChoiceList\\LazyChoiceList', 'Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\ObjectChoiceList' => 'Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList', 'Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\SimpleChoiceList' => 'Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList', 'Symfony\\Component\\Form\\ChoiceList\\ArrayKeyChoiceList' => 'Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList']);
};
