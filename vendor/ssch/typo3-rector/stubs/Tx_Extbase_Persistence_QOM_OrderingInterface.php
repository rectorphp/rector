<?php

namespace RectorPrefix20210922;

if (\interface_exists('Tx_Extbase_Persistence_QOM_OrderingInterface')) {
    return;
}
interface Tx_Extbase_Persistence_QOM_OrderingInterface
{
}
\class_alias('Tx_Extbase_Persistence_QOM_OrderingInterface', 'Tx_Extbase_Persistence_QOM_OrderingInterface', \false);
