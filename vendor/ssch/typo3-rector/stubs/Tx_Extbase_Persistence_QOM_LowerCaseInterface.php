<?php

namespace RectorPrefix20211020;

if (\interface_exists('Tx_Extbase_Persistence_QOM_LowerCaseInterface')) {
    return;
}
interface Tx_Extbase_Persistence_QOM_LowerCaseInterface
{
}
\class_alias('Tx_Extbase_Persistence_QOM_LowerCaseInterface', 'Tx_Extbase_Persistence_QOM_LowerCaseInterface', \false);
