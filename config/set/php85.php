<?php

declare (strict_types=1);
namespace RectorPrefix202508;

use Rector\Config\RectorConfig;
use Rector\Php85\Rector\ArrayDimFetch\ArrayFirstLastRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ArrayFirstLastRector::class]);
    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [new RemoveFuncCallArg('openssl_pkey_derive', 2)]);
    // https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_splobjectstoragecontains_splobjectstorageattach_and_splobjectstoragedetach
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('SplObjectStorage', 'contains', 'offsetExists'), new MethodCallRename('SplObjectStorage', 'attach', 'offsetSet'), new MethodCallRename('SplObjectStorage', 'detach', 'offsetUnset')]);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        // https://wiki.php.net/rfc/deprecations_php_8_5#formally_deprecate_socket_set_timeout
        'socket_set_timeout' => 'stream_set_timeout',
        // https://wiki.php.net/rfc/deprecations_php_8_5#formally_deprecate_mysqli_execute
        'mysqli_execute' => 'mysqli_stmt_execute',
    ]);
};
