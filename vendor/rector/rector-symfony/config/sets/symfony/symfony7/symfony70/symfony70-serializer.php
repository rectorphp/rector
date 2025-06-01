<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#frameworkbundle
        'Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer' => 'Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface',
        'Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer' => 'Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface',
    ]);
};
