<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\Visibility;
use Rector\Tests\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ChangeMethodVisibilityRector::class)
        ->configure([

            new ChangeMethodVisibility(ParentObject::class, 'toBePublicMethod', Visibility::PUBLIC),
            new ChangeMethodVisibility(ParentObject::class, 'toBeProtectedMethod', Visibility::PROTECTED),
            new ChangeMethodVisibility(ParentObject::class, 'toBePrivateMethod', Visibility::PRIVATE),
            new ChangeMethodVisibility(ParentObject::class, 'toBePublicStaticMethod', Visibility::PUBLIC),

        ]);
};
