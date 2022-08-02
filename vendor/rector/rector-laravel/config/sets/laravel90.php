<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\Visibility;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
# see https://laravel.com/docs/9.x/upgrade
return static function (RectorConfig $rectorConfig) : void {
    // https://github.com/laravel/framework/commit/8f9ddea4481717943ed4ecff96d86b703c81a87d
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Illuminate\\Contracts\\Foundation\\Application', 'storagePath', 0, 'path', '')]);
    // https://github.com/laravel/framework/commit/e6c8aaea886d35cc55bd3469f1a95ad56d53e474
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Illuminate\\Foundation\\Application', 'langPath', 0, 'path', '')]);
    // https://github.com/laravel/framework/commit/e095ac0e928b5620f33c9b60816fde5ece867d32
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Illuminate\\Database\\Eloquent\\Model', 'touch', 0, 'attribute')]);
    // https://github.com/laravel/framework/commit/6daecf43dd931dc503e410645ff4a7d611e3371f
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Illuminate\\Queue\\Failed\\FailedJobProviderInterface', 'flush', 0, 'hours')]);
    // https://github.com/laravel/framework/commit/8b40e8b7cba2fbf8337dfc05e3c6a62ae457e889
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Illuminate\\Foundation\\Http\\FormRequest', 'validated', 0, 'key'), new ArgumentAdder('Illuminate\\Foundation\\Http\\FormRequest', 'validated', 1, 'default')]);
    // https://github.com/laravel/framework/commit/84c78b9f5f3dad58f92161069e6482f7267ffdb6
    $rectorConfig->ruleWithConfiguration(ChangeMethodVisibilityRector::class, [new ChangeMethodVisibility('Illuminate\\Foundation\\Exceptions\\Handler', 'ignore', Visibility::PUBLIC)]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // https://github.com/laravel/framework/commit/9b4f011fb95c70444812f61d46c8e21fb5b66dd9
        new MethodCallRename('Illuminate\\Support\\Enumerable', 'reduceWithKeys', 'reduce'),
        // https://github.com/laravel/framework/commit/02365bb5ebafeeaef28b5eb659466c56b2634c65
        new MethodCallRename('Illuminate\\Support\\Enumerable', 'reduceMany', 'reduceSpread'),
        // https://github.com/laravel/framework/commit/097107ab50ce754c709313fc75a6f1f4a9389bfc
        new MethodCallRename('Illuminate\\Mail\\Message', 'getSwiftMessage', 'getSymfonyMessage'),
        // https://github.com/laravel/framework/commit/097107ab50ce754c709313fc75a6f1f4a9389bfc
        new MethodCallRename('Illuminate\\Mail\\Mailable', 'withSwiftMessage', 'withSymfonyMessage'),
        // https://github.com/laravel/framework/commit/097107ab50ce754c709313fc75a6f1f4a9389bfc
        new MethodCallRename('Illuminate\\Notifications\\Messages\\MailMessage', 'withSwiftMessage', 'withSymfonyMessage'),
        // https://github.com/laravel/framework/commit/097107ab50ce754c709313fc75a6f1f4a9389bfc
        new MethodCallRename('Illuminate\\Mail\\Mailer', 'getSwiftMailer', 'getSymfonyTransport'),
        // https://github.com/laravel/framework/commit/097107ab50ce754c709313fc75a6f1f4a9389bfc
        new MethodCallRename('Illuminate\\Mail\\Mailer', 'setSwiftMailer', 'setSymfonyTransport'),
        // https://github.com/laravel/framework/commit/097107ab50ce754c709313fc75a6f1f4a9389bfc
        new MethodCallRename('Illuminate\\Mail\\MailManager', 'createTransport', 'createSymfonyTransport'),
        // https://github.com/laravel/framework/commit/59ff96c269f691bfd197090675c0235700f750b2
        // https://github.com/laravel/framework/commit/9894c2c64dc70f7dfda2ac46dfdaa8769ce4596a
        new MethodCallRename('Illuminate\\Testing\\TestResponse', 'assertDeleted', 'assertModelMissing'),
    ]);
};
