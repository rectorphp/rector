<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\RectorRuleAndValueObjectHaveSameStartsRule\Fixture;

use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\PHPStanExtensions\Tests\Rule\RectorRuleAndValueObjectHaveSameStartsRule\Source\ConfigureValueObject;
use function Rector\SymfonyPhpConfig\inline_value_objects;

return static function (object $random): void {
    $random->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_VISIBILITIES => inline_value_objects([
                new ConfigureValueObject(),
                new ConfigureValueObject(),
            ]),
        ]]);
};
