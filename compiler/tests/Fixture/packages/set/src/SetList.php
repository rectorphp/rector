<?php

declare(strict_types=1);

namespace Rector\Compiler\Tests\Fixture\packages\set\src;

final class SetList
{
    public function __construct()
    {
        $setNamesToSetPaths = [
            \Rector\Set\ValueObject\SetList::ACTION_INJECTION_TO_CONSTRUCTOR_INJECTION => __DIR__ . '/../../../config/set/action-injection-to-constructor-injection.php',
        ];
    }
}
