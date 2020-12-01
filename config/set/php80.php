<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php80\Rector\FuncCall\TokenGetAllToObjectRector;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php80\Rector\Ternary\GetDebugTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(UnionTypesRector::class);

    $services->set(StrContainsRector::class);

    $services->set(StrStartsWithRector::class);

    $services->set(StrEndsWithRector::class);

    $services->set(StringableForToStringRector::class);

    $services->set(AnnotationToAttributeRector::class);

    $services->set(ClassOnObjectRector::class);

    $services->set(GetDebugTypeRector::class);

    $services->set(TokenGetAllToObjectRector::class);

    $services->set(RemoveUnusedVariableInCatchRector::class);

    $services->set(ClassPropertyAssignToConstructorPromotionRector::class);

    $services->set(ChangeSwitchToMatchRector::class);

    // nette\utils and Strings::replace()
    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::ADDED_ARGUMENTS => ValueObjectInliner::inline([
                new ArgumentAdder('Nette\Utils\Strings', 'replace', 2, 'replacement', ''),
            ]),
        ]]);
};
