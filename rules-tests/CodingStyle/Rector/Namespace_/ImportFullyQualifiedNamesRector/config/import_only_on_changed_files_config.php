<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\NormalParamClass;
use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\NormalReturnClass;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $rectorConfig->importNames();
    $parameters->set(Option::APPLY_AUTO_IMPORT_NAMES_ON_CHANGED_FILES_ONLY, true);

    $rectorConfig
        ->ruleWithConfiguration(RenameClassRector::class, [
            NormalParamClass::class => NormalReturnClass::class,
        ]);
};
