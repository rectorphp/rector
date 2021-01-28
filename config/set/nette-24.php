<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Generic\Rector\Class_\ParentClassToTraitsRector;
use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector;
use Rector\Generic\ValueObject\ArgumentDefaultValueReplacer;
use Rector\Nette\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector;
use Rector\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector;
use Rector\Nette\Rector\MethodCall\AddNextrasDatePickerToDateControlRector;
use Rector\Nette\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector;
use Rector\Nette\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector;
use Rector\Nette\Rector\MethodCall\MergeDefaultsInGetConfigCompilerExtensionRector;
use Rector\Nette\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector;
use Rector\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

// @see https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ParentClassToTraitsRector::class)
        ->call('configure', [[
            ParentClassToTraitsRector::PARENT_CLASS_TO_TRAITS => [
                'Nette\Object' => ['Nette\SmartObject']
            ]
        ]]);
};
