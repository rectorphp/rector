<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector;
use Rector\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector;
use Rector\Php80\Rector\ClassMethod\SetStateToStaticRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector;
use Rector\Php80\Rector\FuncCall\TokenGetAllToObjectRector;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php80\Rector\Ternary\GetDebugTypeRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Php80\Rector\FunctionLike\UnionTypesRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\NotIdentical\StrContainsRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\Identical\StrStartsWithRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\Identical\StrEndsWithRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector::class, [new \Rector\Transform\ValueObject\StaticCallToFuncCall('Nette\\Utils\\Strings', 'startsWith', 'str_starts_with'), new \Rector\Transform\ValueObject\StaticCallToFuncCall('Nette\\Utils\\Strings', 'endsWith', 'str_ends_with'), new \Rector\Transform\ValueObject\StaticCallToFuncCall('Nette\\Utils\\Strings', 'contains', 'str_contains')]);
    $rectorConfig->rule(\Rector\Php80\Rector\Class_\StringableForToStringRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\FuncCall\ClassOnObjectRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\Ternary\GetDebugTypeRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\FuncCall\TokenGetAllToObjectRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector::class);
    // nette\utils and Strings::replace()
    $rectorConfig->ruleWithConfiguration(\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector::class, [new \Rector\Arguments\ValueObject\ArgumentAdder('Nette\\Utils\\Strings', 'replace', 2, 'replacement', '')]);
    $rectorConfig->rule(\Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\ClassMethod\SetStateToStaticRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector::class);
    // @see https://php.watch/versions/8.0/pgsql-aliases-deprecated
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class, ['pg_clientencoding' => 'pg_client_encoding', 'pg_cmdtuples' => 'pg_affected_rows', 'pg_errormessage' => 'pg_last_error', 'pg_fieldisnull' => 'pg_field_is_null', 'pg_fieldname' => 'pg_field_name', 'pg_fieldnum' => 'pg_field_num', 'pg_fieldprtlen' => 'pg_field_prtlen', 'pg_fieldsize' => 'pg_field_size', 'pg_fieldtype' => 'pg_field_type', 'pg_freeresult' => 'pg_free_result', 'pg_getlastoid' => 'pg_last_oid', 'pg_loclose' => 'pg_lo_close', 'pg_locreate' => 'pg_lo_create', 'pg_loexport' => 'pg_lo_export', 'pg_loimport' => 'pg_lo_import', 'pg_loopen' => 'pg_lo_open', 'pg_loread' => 'pg_lo_read', 'pg_loreadall' => 'pg_lo_read_all', 'pg_lounlink' => 'pg_lo_unlink', 'pg_lowrite' => 'pg_lo_write', 'pg_numfields' => 'pg_num_fields', 'pg_numrows' => 'pg_num_rows', 'pg_result' => 'pg_fetch_result', 'pg_setclientencoding' => 'pg_set_client_encoding']);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector::class, [new \Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'gte', 'ge'), new \Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'lte', 'le'), new \Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue('version_compare', 2, '', '!='), new \Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue('version_compare', 2, '!', '!='), new \Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'g', 'gt'), new \Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'l', 'lt'), new \Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'gte', 'ge'), new \Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'lte', 'le'), new \Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'n', 'ne')]);
    $rectorConfig->rule(\Rector\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector::class);
    $rectorConfig->rule(\Rector\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector::class);
};
