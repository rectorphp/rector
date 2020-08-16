<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\SwapFuncCallArgumentsRector;
use Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector;
use Rector\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector;
use Rector\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector;
use Rector\MysqlToMysqli\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector;
use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # https://stackoverflow.com/a/1390625/1348344
    # https://github.com/philip/MySQLConverterTool/blob/master/Converter.php
    # https://www.phpclasses.org/blog/package/9199/post/3-Smoothly-Migrate-your-PHP-Code-using-the-Old-MySQL-extension-to-MySQLi.html
    $services->set(MysqlAssignToMysqliRector::class);

    $services->set(MysqlFuncCallToMysqliRector::class);

    $services->set(MysqlPConnectToMysqliConnectRector::class);

    # first swap arguments, then rename
    $services->set(SwapFuncCallArgumentsRector::class)
        ->call('configure', [[
            SwapFuncCallArgumentsRector::NEW_ARGUMENT_POSITIONS_BY_FUNCTION_NAME => [
                'mysql_real_escape_string' => [1, 0],
                'mysql_select_db' => [1, 0],
                'mysql_set_charset' => [1, 0],
                'mysql_query' => [1, 0],
                'mysql_fetch_row' => [1, 0],
            ],
        ]]);

    $services->set(RenameFunctionRector::class)
        ->call('configure', [[
            RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => [
                'mysql_affected_rows' => 'mysqli_affected_rows',
                'mysql_close' => 'mysqli_close',
                'mysql_data_seek' => 'mysqli_data_seek',
                'mysql_errno' => 'mysqli_errno',
                'mysql_fetch_array' => 'mysqli_fetch_array',
                'mysql_fetch_assoc' => 'mysqli_fetch_assoc',
                'mysql_fetch_lengths' => 'mysqli_fetch_lengths',
                'mysql_fetch_object' => 'mysqli_fetch_object',
                'mysql_fetch_row' => 'mysqli_fetch_row',
                'mysql_field_seek' => 'mysqli_field_seek',
                'mysql_free_result' => 'mysqli_free_result',
                'mysql_get_client_info' => 'mysqli_get_client_info',
                'mysql_get_host_info' => 'mysqli_get_host_info',
                'mysql_get_proto_info' => 'mysqli_get_proto_info',
                'mysql_get_server_info' => 'mysqli_get_server_info',
                'mysql_info' => 'mysqli_info',
                'mysql_insert_id' => 'mysqli_insert_id',
                'mysql_num_rows' => 'mysqli_num_rows',
                'mysql_ping' => 'mysqli_ping',
                'mysql_real_escape_string' => 'mysqli_real_escape_string',
                'mysql_select_db' => 'mysqli_select_db',
                'mysql_set_charset' => 'mysqli_set_charset',
                'mysql_stat' => 'mysqli_stat',
                'mysql_thread_id' => 'mysqli_thread_id',
                'mysql_numfields' => 'mysqli_num_fields',
                'mysql_escape_string' => 'mysqli_real_escape_string',
                'mysql_client_encoding' => 'mysqli_character_set_name',
                'mysql_numrows' => 'mysqli_num_rows',
                'mysql_list_processes' => 'mysqli_thread_id',
                'mysql_num_fields' => 'mysqli_field_count',
                'mysql_connect' => 'mysqli_connect',
            ],
        ]]);

    # http://php.net/manual/en/mysql.constants.php → http://php.net/manual/en/mysqli.constants.php
    $services->set(RenameConstantRector::class)
        ->call('configure', [[
            RenameConstantRector::OLD_TO_NEW_CONSTANTS => [
                'MYSQL_ASSOC' => 'MYSQLI_ASSOC',
                'MYSQL_NUM' => 'MYSQLI_NUM',
                'MYSQL_BOTH' => 'MYSQLI_BOTH',
                'MYSQL_CLIENT_COMPRESS' => 'MYSQLI_CLIENT_COMPRESS',
                'MYSQL_CLIENT_IGNORE_SPACE' => 'MYSQLI_CLIENT_IGNORE_SPACE',
                'MYSQL_CLIENT_INTERACTIVE' => 'MYSQLI_CLIENT_INTERACTIVE',
                'MYSQL_CLIENT_SSL' => 'MYSQLI_CLIENT_SSL',
                'MYSQL_PRIMARY_KEY_FLAG' => 'MYSQLI_PRI_KEY_FLAG',
            ],
        ]]);

    $services->set(MysqlQueryMysqlErrorWithLinkRector::class);
};
