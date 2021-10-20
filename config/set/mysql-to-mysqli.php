<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Arguments\Rector\FuncCall\SwapFuncCallArgumentsRector;
use Rector\Arguments\ValueObject\SwapFuncCallArguments;
use Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector;
use Rector\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector;
use Rector\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector;
use Rector\MysqlToMysqli\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    # https://stackoverflow.com/a/1390625/1348344
    # https://github.com/philip/MySQLConverterTool/blob/master/Converter.php
    # https://www.phpclasses.org/blog/package/9199/post/3-Smoothly-Migrate-your-PHP-Code-using-the-Old-MySQL-extension-to-MySQLi.html
    $services->set(\Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector::class);
    $services->set(\Rector\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector::class);
    $services->set(\Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector::class)->call('configure', [[\Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector::REMOVED_FUNCTION_ARGUMENTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Removing\ValueObject\RemoveFuncCallArg('mysql_pconnect', 3), new \Rector\Removing\ValueObject\RemoveFuncCallArg('mysql_connect', 3), new \Rector\Removing\ValueObject\RemoveFuncCallArg('mysql_connect', 4)])]]);
    $services->set(\Rector\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector::class);
    # first swap arguments, then rename
    $services->set(\Rector\Arguments\Rector\FuncCall\SwapFuncCallArgumentsRector::class)->call('configure', [[\Rector\Arguments\Rector\FuncCall\SwapFuncCallArgumentsRector::FUNCTION_ARGUMENT_SWAPS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Arguments\ValueObject\SwapFuncCallArguments('mysql_query', [1, 0]), new \Rector\Arguments\ValueObject\SwapFuncCallArguments('mysql_real_escape_string', [1, 0]), new \Rector\Arguments\ValueObject\SwapFuncCallArguments('mysql_select_db', [1, 0]), new \Rector\Arguments\ValueObject\SwapFuncCallArguments('mysql_set_charset', [1, 0])])]]);
    $services->set(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class)->call('configure', [[\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => ['mysql_connect' => 'mysqli_connect', 'mysql_data_seek' => 'mysqli_data_seek', 'mysql_fetch_array' => 'mysqli_fetch_array', 'mysql_fetch_assoc' => 'mysqli_fetch_assoc', 'mysql_fetch_lengths' => 'mysqli_fetch_lengths', 'mysql_fetch_object' => 'mysqli_fetch_object', 'mysql_fetch_row' => 'mysqli_fetch_row', 'mysql_field_seek' => 'mysqli_field_seek', 'mysql_free_result' => 'mysqli_free_result', 'mysql_get_client_info' => 'mysqli_get_client_info', 'mysql_num_fields' => 'mysqli_num_fields', 'mysql_numfields' => 'mysqli_num_fields', 'mysql_num_rows' => 'mysqli_num_rows', 'mysql_numrows' => 'mysqli_num_rows']]]);
    # http://php.net/manual/en/mysql.constants.php → http://php.net/manual/en/mysqli.constants.php
    $services->set(\Rector\Renaming\Rector\ConstFetch\RenameConstantRector::class)->call('configure', [[\Rector\Renaming\Rector\ConstFetch\RenameConstantRector::OLD_TO_NEW_CONSTANTS => ['MYSQL_ASSOC' => 'MYSQLI_ASSOC', 'MYSQL_BOTH' => 'MYSQLI_BOTH', 'MYSQL_CLIENT_COMPRESS' => 'MYSQLI_CLIENT_COMPRESS', 'MYSQL_CLIENT_IGNORE_SPACE' => 'MYSQLI_CLIENT_IGNORE_SPACE', 'MYSQL_CLIENT_INTERACTIVE' => 'MYSQLI_CLIENT_INTERACTIVE', 'MYSQL_CLIENT_SSL' => 'MYSQLI_CLIENT_SSL', 'MYSQL_NUM' => 'MYSQLI_NUM', 'MYSQL_PRIMARY_KEY_FLAG' => 'MYSQLI_PRI_KEY_FLAG']]]);
    $services->set(\Rector\MysqlToMysqli\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector::class);
};
