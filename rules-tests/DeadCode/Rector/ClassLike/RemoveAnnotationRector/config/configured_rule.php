<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassLike\RemoveAnnotationRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RemoveAnnotationRector::class)
        ->configure(['method', 'JMS\DiExtraBundle\Annotation\InjectParams']);
};
