<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class SkipMixed
{
    /**
     * @var mixed[]
     */
    private const COMPOSER_JSON_DATA = [
        'require' => [
            'phpunit/phpunit' => 'v1.0.0',
            'rector/rector' => 'v1.0.0',
        ],
        'autoload-dev' => [
            'psr-4' => [
                'Symplify\Tests\\' => 'tests',
                'Symplify\SuperTests\\' => 'super-tests',
            ],
            'files' => ['src/SomeFile.php', 'src/KeepFile.php'],
        ],
    ];
}
