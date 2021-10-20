<?php

namespace RectorPrefix20211020\TYPO3\CMS\Extbase\SignalSlot;

if (\class_exists('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')) {
    return;
}
class Dispatcher
{
    /**
     * @return void
     */
    public function connect($signalClassName, $signalName, $slotClassNameOrObject, $slotMethodName = '', $passSignalInformation = \true)
    {
    }
}
