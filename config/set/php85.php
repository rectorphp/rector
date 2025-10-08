<?php

declare (strict_types=1);
namespace RectorPrefix202510;

use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\String_;
use Rector\Config\RectorConfig;
use Rector\Php85\Rector\ArrayDimFetch\ArrayFirstLastRector;
use Rector\Php85\Rector\Class_\SleepToSerializeRector;
use Rector\Php85\Rector\Class_\WakeupToUnserializeRector;
use Rector\Php85\Rector\ClassMethod\NullDebugInfoReturnRector;
use Rector\Php85\Rector\Const_\DeprecatedAnnotationToDeprecatedAttributeRector;
use Rector\Php85\Rector\FuncCall\ArrayKeyExistsNullToEmptyStringRector;
use Rector\Php85\Rector\FuncCall\ChrArgModuloRector;
use Rector\Php85\Rector\FuncCall\OrdSingleByteRector;
use Rector\Php85\Rector\FuncCall\RemoveFinfoBufferContextArgRector;
use Rector\Php85\Rector\ShellExec\ShellExecFunctionCallOverBackticksRector;
use Rector\Php85\Rector\Switch_\ColonAfterSwitchCaseRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\Cast\RenameCastRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameCast;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Transform\Rector\FuncCall\WrapFuncCallWithPhpVersionIdCheckerRector;
use Rector\Transform\ValueObject\WrapFuncCallWithPhpVersionIdChecker;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ArrayFirstLastRector::class, RemoveFinfoBufferContextArgRector::class, NullDebugInfoReturnRector::class, DeprecatedAnnotationToDeprecatedAttributeRector::class, ColonAfterSwitchCaseRector::class, ArrayKeyExistsNullToEmptyStringRector::class, ChrArgModuloRector::class, SleepToSerializeRector::class, OrdSingleByteRector::class, WakeupToUnserializeRector::class, ShellExecFunctionCallOverBackticksRector::class]);
    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        // https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_key_length_parameter_of_openssl_pkey_derive
        new RemoveFuncCallArg('openssl_pkey_derive', 2),
        // https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_the_exclude_disabled_parameter_of_get_defined_functions
        new RemoveFuncCallArg('get_defined_functions', 0),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_splobjectstoragecontains_splobjectstorageattach_and_splobjectstoragedetach
        new MethodCallRename('SplObjectStorage', 'contains', 'offsetExists'),
        new MethodCallRename('SplObjectStorage', 'attach', 'offsetSet'),
        new MethodCallRename('SplObjectStorage', 'detach', 'offsetUnset'),
        // https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_driver_specific_pdo_constants_and_methods
        new MethodCallRename('PDO', 'pgsqlCopyFromArray', 'copyFromArray'),
        new MethodCallRename('PDO', 'pgsqlCopyFromFile', 'copyFromFile'),
        new MethodCallRename('PDO', 'pgsqlCopyToArray', 'copyToArray'),
        new MethodCallRename('PDO', 'pgsqlCopyToFile', 'copyToFile'),
        new MethodCallRename('PDO', 'pgsqlGetNotify', 'getNotify'),
        new MethodCallRename('PDO', 'pgsqlGetPid', 'getPid'),
        new MethodCallRename('PDO', 'pgsqlLOBCreate', 'lobCreate'),
        new MethodCallRename('PDO', 'pgsqlLOBOpen', 'lobOpen'),
        new MethodCallRename('PDO', 'pgsqlLOBUnlink', 'lobUnlink'),
        new MethodCallRename('PDO', 'sqliteCreateAggregate', 'createAggregate'),
        new MethodCallRename('PDO', 'sqliteCreateCollation', 'createCollation'),
        new MethodCallRename('PDO', 'sqliteCreateFunction', 'createFunction'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        // https://wiki.php.net/rfc/deprecations_php_8_5#formally_deprecate_socket_set_timeout
        'socket_set_timeout' => 'stream_set_timeout',
        // https://wiki.php.net/rfc/deprecations_php_8_5#formally_deprecate_mysqli_execute
        'mysqli_execute' => 'mysqli_stmt_execute',
    ]);
    // https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_driver_specific_pdo_constants_and_methods
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('PDO', 'DBLIB_ATTR_CONNECTION_TIMEOUT', 'Pdo\Dblib', 'ATTR_CONNECTION_TIMEOUT'), new RenameClassAndConstFetch('PDO', 'DBLIB_ATTR_QUERY_TIMEOUT', 'Pdo\Dblib', 'ATTR_QUERY_TIMEOUT'), new RenameClassAndConstFetch('PDO', 'DBLIB_ATTR_STRINGIFY_UNIQUEIDENTIFIER', 'Pdo\Dblib', 'ATTR_STRINGIFY_UNIQUEIDENTIFIER'), new RenameClassAndConstFetch('PDO', 'DBLIB_ATTR_VERSION', 'Pdo\Dblib', 'ATTR_VERSION'), new RenameClassAndConstFetch('PDO', 'DBLIB_ATTR_TDS_VERSION', 'Pdo\Dblib', 'ATTR_TDS_VERSION'), new RenameClassAndConstFetch('PDO', 'DBLIB_ATTR_SKIP_EMPTY_ROWSETS', 'Pdo\Dblib', 'ATTR_SKIP_EMPTY_ROWSETS'), new RenameClassAndConstFetch('PDO', 'DBLIB_ATTR_DATETIME_CONVERT', 'Pdo\Dblib', 'ATTR_DATETIME_CONVERT'), new RenameClassAndConstFetch('PDO', 'FB_ATTR_DATE_FORMAT', 'Pdo\Firebird', 'ATTR_DATE_FORMAT'), new RenameClassAndConstFetch('PDO', 'FB_ATTR_TIME_FORMAT', 'Pdo\Firebird', 'ATTR_TIME_FORMAT'), new RenameClassAndConstFetch('PDO', 'FB_ATTR_TIMESTAMP_FORMAT', 'Pdo\Firebird', 'ATTR_TIMESTAMP_FORMAT'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_USE_BUFFERED_QUERY', 'Pdo\Mysql', 'ATTR_USE_BUFFERED_QUERY'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_LOCAL_INFILE', 'Pdo\Mysql', 'ATTR_LOCAL_INFILE'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_LOCAL_INFILE_DIRECTORY', 'Pdo\Mysql', 'ATTR_LOCAL_INFILE_DIRECTORY'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_INIT_COMMAND', 'Pdo\Mysql', 'ATTR_INIT_COMMAND'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_MAX_BUFFER_SIZE', 'Pdo\Mysql', 'ATTR_MAX_BUFFER_SIZE'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_READ_DEFAULT_FILE', 'Pdo\Mysql', 'ATTR_READ_DEFAULT_FILE'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_READ_DEFAULT_GROUP', 'Pdo\Mysql', 'ATTR_READ_DEFAULT_GROUP'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_COMPRESS', 'Pdo\Mysql', 'ATTR_COMPRESS'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_DIRECT_QUERY', 'Pdo\Mysql', 'ATTR_DIRECT_QUERY'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_FOUND_ROWS', 'Pdo\Mysql', 'ATTR_FOUND_ROWS'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_IGNORE_SPACE', 'Pdo\Mysql', 'ATTR_IGNORE_SPACE'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_SSL_KEY', 'Pdo\Mysql', 'ATTR_SSL_KEY'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_SSL_CERT', 'Pdo\Mysql', 'ATTR_SSL_CERT'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_SSL_CA', 'Pdo\Mysql', 'ATTR_SSL_CA'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_SSL_CAPATH', 'Pdo\Mysql', 'ATTR_SSL_CAPATH'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_SSL_CIPHER', 'Pdo\Mysql', 'ATTR_SSL_CIPHER'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_SSL_VERIFY_SERVER_CERT', 'Pdo\Mysql', 'ATTR_SSL_VERIFY_SERVER_CERT'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_SERVER_PUBLIC_KEY', 'Pdo\Mysql', 'ATTR_SERVER_PUBLIC_KEY'), new RenameClassAndConstFetch('PDO', 'MYSQL_ATTR_MULTI_STATEMENTS', 'Pdo\Mysql', 'ATTR_MULTI_STATEMENTS'), new RenameClassAndConstFetch('PDO', 'ODBC_ATTR_USE_CURSOR_LIBRARY', 'Pdo\Odbc', 'ATTR_USE_CURSOR_LIBRARY'), new RenameClassAndConstFetch('PDO', 'ODBC_ATTR_ASSUME_UTF8', 'Pdo\Odbc', 'ATTR_ASSUME_UTF8'), new RenameClassAndConstFetch('PDO', 'ODBC_SQL_USE_IF_NEEDED', 'Pdo\Odbc', 'SQL_USE_IF_NEEDED'), new RenameClassAndConstFetch('PDO', 'ODBC_SQL_USE_DRIVER', 'Pdo\Odbc', 'SQL_USE_DRIVER'), new RenameClassAndConstFetch('PDO', 'ODBC_SQL_USE_ODBC', 'Pdo\Odbc', 'SQL_USE_ODBC'), new RenameClassAndConstFetch('PDO', 'PGSQL_ATTR_DISABLE_PREPARES', 'Pdo\Pgsql', 'ATTR_DISABLE_PREPARES'), new RenameClassAndConstFetch('PDO', 'SQLITE_ATTR_EXTENDED_RESULT_CODES', 'Pdo\Sqlite', 'ATTR_EXTENDED_RESULT_CODES'), new RenameClassAndConstFetch('PDO', 'SQLITE_ATTR_OPEN_FLAGS', 'Pdo\Sqlite', 'OPEN_FLAGS'), new RenameClassAndConstFetch('PDO', 'SQLITE_ATTR_READONLY_STATEMENT', 'Pdo\Sqlite', 'ATTR_READONLY_STATEMENT'), new RenameClassAndConstFetch('PDO', 'SQLITE_DETERMINISTIC', 'Pdo\Sqlite', 'DETERMINISTIC'), new RenameClassAndConstFetch('PDO', 'SQLITE_OPEN_READONLY', 'Pdo\Sqlite', 'OPEN_READONLY'), new RenameClassAndConstFetch('PDO', 'SQLITE_OPEN_READWRITE', 'Pdo\Sqlite', 'OPEN_READWRITE'), new RenameClassAndConstFetch('PDO', 'SQLITE_OPEN_CREATE', 'Pdo\Sqlite', 'OPEN_CREATE')]);
    // https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_non-standard_cast_names
    $rectorConfig->ruleWithConfiguration(RenameCastRector::class, [new RenameCast(Int_::class, Int_::KIND_INTEGER, Int_::KIND_INT), new RenameCast(Bool_::class, Bool_::KIND_BOOLEAN, Bool_::KIND_BOOL), new RenameCast(Double::class, Double::KIND_DOUBLE, Double::KIND_FLOAT), new RenameCast(String_::class, String_::KIND_BINARY, String_::KIND_STRING)]);
    // https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_no-op_functions_from_the_resource_to_object_conversion
    $rectorConfig->ruleWithConfiguration(WrapFuncCallWithPhpVersionIdCheckerRector::class, [new WrapFuncCallWithPhpVersionIdChecker('curl_close', 80500), new WrapFuncCallWithPhpVersionIdChecker('curl_share_close', 80500), new WrapFuncCallWithPhpVersionIdChecker('finfo_close', 80500), new WrapFuncCallWithPhpVersionIdChecker('imagedestroy', 80500), new WrapFuncCallWithPhpVersionIdChecker('xml_parser_free', 80500)]);
    // https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_filter_default_constant
    $rectorConfig->ruleWithConfiguration(RenameConstantRector::class, ['FILTER_DEFAULT' => 'FILTER_UNSAFE_RAW']);
};
