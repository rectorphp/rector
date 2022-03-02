<?php

declare (strict_types=1);
namespace Rector\DeadCode\SideEffect;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class PureFunctionDetector
{
    /**
     * @see https://github.com/vimeo/psalm/blob/d470903722cfcbc1cd04744c5491d3e6d13ec3d9/src/Psalm/Internal/Codebase/Functions.php#L288
     * @var string[]
     */
    private const IMPURE_FUNCTIONS = [
        'chdir',
        'chgrp',
        'chmod',
        'chown',
        'chroot',
        'closedir',
        'copy',
        'file_put_contents',
        'fopen',
        'fread',
        'fwrite',
        'fclose',
        'touch',
        'fpassthru',
        'fputs',
        'fscanf',
        'fseek',
        'ftruncate',
        'fprintf',
        'symlink',
        'mkdir',
        'unlink',
        'rename',
        'rmdir',
        'popen',
        'pclose',
        'fputcsv',
        'umask',
        'finfo_close',
        'readline_add_history',
        'stream_set_timeout',
        'fflush',
        // stream/socket io
        'stream_context_set_option',
        'socket_write',
        'stream_set_blocking',
        'socket_close',
        'socket_set_option',
        'stream_set_write_buffer',
        // meta calls
        'call_user_func',
        'call_user_func_array',
        'define',
        'create_function',
        // http
        'header',
        'header_remove',
        'http_response_code',
        'setcookie',
        // output buffer
        'ob_start',
        'ob_end_clean',
        'ob_get_clean',
        'readfile',
        'printf',
        'var_dump',
        'phpinfo',
        'ob_implicit_flush',
        'vprintf',
        // mcrypt
        'mcrypt_generic_init',
        'mcrypt_generic_deinit',
        'mcrypt_module_close',
        // internal optimisation
        'opcache_compile_file',
        'clearstatcache',
        // process-related
        'pcntl_signal',
        'posix_kill',
        'cli_set_process_title',
        'pcntl_async_signals',
        'proc_close',
        'proc_nice',
        'proc_open',
        'proc_terminate',
        // curl
        'curl_setopt',
        'curl_close',
        'curl_multi_add_handle',
        'curl_multi_remove_handle',
        'curl_multi_select',
        'curl_multi_close',
        'curl_setopt_array',
        // apc, apcu
        'apc_store',
        'apc_delete',
        'apc_clear_cache',
        'apc_add',
        'apc_inc',
        'apc_dec',
        'apc_cas',
        'apcu_store',
        'apcu_delete',
        'apcu_clear_cache',
        'apcu_add',
        'apcu_inc',
        'apcu_dec',
        'apcu_cas',
        // gz
        'gzwrite',
        'gzrewind',
        'gzseek',
        'gzclose',
        // newrelic
        'newrelic_start_transaction',
        'newrelic_name_transaction',
        'newrelic_add_custom_parameter',
        'newrelic_add_custom_tracer',
        'newrelic_background_job',
        'newrelic_end_transaction',
        'newrelic_set_appname',
        // execution
        'shell_exec',
        'exec',
        'system',
        'passthru',
        'pcntl_exec',
        // well-known functions
        'libxml_use_internal_errors',
        'libxml_disable_entity_loader',
        'curl_exec',
        'mt_srand',
        'openssl_pkcs7_sign',
        'openssl_sign',
        'mt_rand',
        'rand',
        'random_int',
        'random_bytes',
        'wincache_ucache_delete',
        'wincache_ucache_set',
        'wincache_ucache_inc',
        'class_alias',
        // php environment
        'ini_set',
        'sleep',
        'usleep',
        'register_shutdown_function',
        'error_reporting',
        'register_tick_function',
        'unregister_tick_function',
        'set_error_handler',
        'user_error',
        'trigger_error',
        'restore_error_handler',
        'date_default_timezone_set',
        'assert_options',
        'setlocale',
        'set_exception_handler',
        'set_time_limit',
        'putenv',
        'spl_autoload_register',
        'microtime',
        'array_rand',
        // logging
        'openlog',
        'syslog',
        'error_log',
        'define_syslog_variables',
        // session
        'session_id',
        'session_name',
        'session_set_cookie_params',
        'session_set_save_handler',
        'session_regenerate_id',
        'mb_internal_encoding',
        'session_start',
        // ldap
        'ldap_set_option',
        // iterators
        'rewind',
        'iterator_apply',
        // mysqli
        'mysqli_select_db',
        'mysqli_dump_debug_info',
        'mysqli_kill',
        'mysqli_multi_query',
        'mysqli_next_result',
        'mysqli_options',
        'mysqli_ping',
        'mysqli_query',
        'mysqli_report',
        'mysqli_rollback',
        'mysqli_savepoint',
        'mysqli_set_charset',
        'mysqli_ssl_set',
        // postgres
        'pg_exec',
        'pg_execute',
        'pg_connect',
        'pg_query_params',
        // ftp
        'ftp_close',
        // bcmath
        'bcscale',
        'bcdiv',
        // json
        'json_encode',
        'json_decode',
        'json_last_error',
        // array
        'array_pop',
        'array_push',
        'array_shift',
        'next',
        'prev',
        // stream
        'stream_filter_append',
    ];
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function detect(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        $funcCallName = $this->nodeNameResolver->getName($funcCall);
        if ($funcCallName === null) {
            return \false;
        }
        $scope = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $name = new \PhpParser\Node\Name($funcCallName);
        $hasFunction = $this->reflectionProvider->hasFunction($name, $scope);
        if (!$hasFunction) {
            return \false;
        }
        $function = $this->reflectionProvider->getFunction($name, $scope);
        if (!$function instanceof \PHPStan\Reflection\Native\NativeFunctionReflection) {
            return \false;
        }
        return !$this->nodeNameResolver->isNames($funcCall, self::IMPURE_FUNCTIONS);
    }
}
