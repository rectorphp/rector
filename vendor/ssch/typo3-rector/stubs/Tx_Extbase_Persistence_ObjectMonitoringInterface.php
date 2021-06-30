<?php

namespace RectorPrefix20210630;

if (\interface_exists('Tx_Extbase_Persistence_ObjectMonitoringInterface')) {
    return;
}
interface Tx_Extbase_Persistence_ObjectMonitoringInterface
{
}
\class_alias('Tx_Extbase_Persistence_ObjectMonitoringInterface', 'Tx_Extbase_Persistence_ObjectMonitoringInterface', \false);
