<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\Visibility;
use Rector\Tests\Visibility\Rector\ClassConst\ChangeConstantVisibilityRector\Source\ParentObject;
use Rector\Visibility\Rector\ClassConst\ChangeConstantVisibilityRector;
use Rector\Visibility\ValueObject\ChangeConstantVisibility;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(ChangeConstantVisibilityRector::class, [
            new ChangeConstantVisibility(ParentObject::class, 'TO_BE_PUBLIC_CONSTANT', Visibility::PUBLIC),
            new ChangeConstantVisibility(ParentObject::class, 'TO_BE_PROTECTED_CONSTANT', Visibility::PROTECTED),
            new ChangeConstantVisibility(ParentObject::class, 'TO_BE_PRIVATE_CONSTANT', Visibility::PRIVATE),
            new ChangeConstantVisibility(
                'Rector\Tests\Visibility\Rector\ClassConst\ChangeConstantVisibilityRector\Fixture\Fixture2',
                'TO_BE_PRIVATE_CONSTANT',
                Visibility::PRIVATE
            ),
        ]);
};
