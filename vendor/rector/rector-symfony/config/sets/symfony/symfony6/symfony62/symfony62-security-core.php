<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
return static function (RectorConfig $rectorConfig) : void {
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46094
        'Symfony\\Component\\Security\\Core\\Security' => 'Symfony\\Bundle\\SecurityBundle\\Security',
    ]);
    // @see https://github.com/symfony/symfony/pull/46094
    // @see https://github.com/symfony/symfony/pull/48554
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Symfony\\Component\\Security\\Core\\Security', 'ACCESS_DENIED_ERROR', 'Symfony\\Component\\Security\\Http\\SecurityRequestAttributes', 'ACCESS_DENIED_ERROR'), new RenameClassAndConstFetch('Symfony\\Component\\Security\\Core\\Security', 'AUTHENTICATION_ERROR', 'Symfony\\Component\\Security\\Http\\SecurityRequestAttributes', 'AUTHENTICATION_ERROR'), new RenameClassAndConstFetch('Symfony\\Component\\Security\\Core\\Security', 'LAST_USERNAME', 'Symfony\\Component\\Security\\Http\\SecurityRequestAttributes', 'LAST_USERNAME'), new RenameClassAndConstFetch('Symfony\\Component\\Security\\Core\\Security', 'MAX_USERNAME_LENGTH', 'Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Badge\\UserBadge', 'MAX_USERNAME_LENGTH')]);
};
