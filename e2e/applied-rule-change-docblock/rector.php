<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector;
use Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'DateTime' => 'DateTimeInterface'
    ]);
    $rectorConfig->ruleWithConfiguration(DowngradeAttributeToAnnotationRector::class, [
        new DowngradeAttributeToAnnotation('Symfony\Component\Routing\Annotation\Route')
    ]);

    $rectorConfig->rule(RemoveUselessVarTagRector::class);
};
