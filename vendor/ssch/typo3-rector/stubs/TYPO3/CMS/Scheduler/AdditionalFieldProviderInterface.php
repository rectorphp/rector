<?php

declare (strict_types=1);
namespace RectorPrefix20210827\TYPO3\CMS\Scheduler;

use RectorPrefix20210827\TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
if (\interface_exists('TYPO3\\CMS\\Scheduler\\AdditionalFieldProviderInterface')) {
    return;
}
interface AdditionalFieldProviderInterface
{
    /**
     * @param mixed[] $taskInfo
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule
     */
    public function getAdditionalFields(&$taskInfo, $task, $schedulerModule);
}
