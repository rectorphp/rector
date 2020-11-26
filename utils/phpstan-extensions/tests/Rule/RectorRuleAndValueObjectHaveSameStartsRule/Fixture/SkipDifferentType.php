<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\RectorRuleAndValueObjectHaveSameStartsRule\Fixture;

use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\PHPStanExtensions\Tests\Rule\RectorRuleAndValueObjectHaveSameStartsRule\Source\ConfigureValueObject;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (object $random): void {
    $random->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_VISIBILITIES => ValueObjectInliner::inline([
                new ConfigureValueObject(),
                new ConfigureValueObject(),
            ]),
        ]]);
};
