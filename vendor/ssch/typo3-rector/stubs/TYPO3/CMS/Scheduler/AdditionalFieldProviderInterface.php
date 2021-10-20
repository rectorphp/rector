<?php

declare (strict_types=1);
namespace RectorPrefix20211020\TYPO3\CMS\Scheduler;

use RectorPrefix20211020\TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
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
