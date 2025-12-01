<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    // @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.4.md#frameworkbundle
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\Bundle\FrameworkBundle\Command\WorkflowDumpCommand' => 'Symfony\Component\Workflow\Command\WorkflowDumpCommand']);
};
