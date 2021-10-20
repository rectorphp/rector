<?php

namespace RectorPrefix20211020;

if (\interface_exists('Tx_Extbase_Persistence_ManagerInterface')) {
    return;
}
interface Tx_Extbase_Persistence_ManagerInterface
{
}
\class_alias('Tx_Extbase_Persistence_ManagerInterface', 'Tx_Extbase_Persistence_ManagerInterface', \false);
