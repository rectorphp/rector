<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector;
use Rector\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\Php80\Rector\ClassMethod\SetStateToStaticRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php80\Rector\FuncCall\TokenGetAllToObjectRector;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php80\Rector\Ternary\GetDebugTypeRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
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
    $services->set(ArgumentAdderRector::class)->call('configure', [[
        ArgumentAdderRector::ADDED_ARGUMENTS => ValueObjectInliner::inline([
            new ArgumentAdder('Nette\Utils\Strings', 'replace', 2, 'replacement', ''),
        ]),
    ]]);
    $services->set(RemoveParentCallWithoutParentRector::class);
    $services->set(SetStateToStaticRector::class);
    $services->set(FinalPrivateToPrivateVisibilityRector::class);
    // @see https://php.watch/versions/8.0/pgsql-aliases-deprecated
    $services->set(RenameFunctionRector::class)->call('configure', [[
        RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => [
            'pg_clientencoding' => 'pg_client_encoding',
            'pg_cmdtuples' => 'pg_affected_rows',
            'pg_errormessage' => 'pg_last_error',
            'pg_fieldisnull' => 'pg_field_is_null',
            'pg_fieldname' => 'pg_field_name',
            'pg_fieldnum' => 'pg_field_num',
            'pg_fieldprtlen' => 'pg_field_prtlen',
            'pg_fieldsize' => 'pg_field_size',
            'pg_fieldtype' => 'pg_field_type',
            'pg_freeresult' => 'pg_free_result',
            'pg_getlastoid' => 'pg_last_oid',
            'pg_loclose' => 'pg_lo_close',
            'pg_locreate' => 'pg_lo_create',
            'pg_loexport' => 'pg_lo_export',
            'pg_loimport' => 'pg_lo_import',
            'pg_loopen' => 'pg_lo_open',
            'pg_loread' => 'pg_lo_read',
            'pg_loreadall' => 'pg_lo_read_all',
            'pg_lounlink' => 'pg_lo_unlink',
            'pg_lowrite' => 'pg_lo_write',
            'pg_numfields' => 'pg_num_fields',
            'pg_numrows' => 'pg_num_rows',
            'pg_result' => 'pg_fetch_result',
            'pg_setclientencoding' => 'pg_set_client_encoding',
        ],
    ]]);
    $services->set(OptionalParametersAfterRequiredRector::class);
};
