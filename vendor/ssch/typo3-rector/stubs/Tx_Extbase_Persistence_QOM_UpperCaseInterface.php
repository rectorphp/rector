<?php

namespace RectorPrefix20210909;

if (\interface_exists('Tx_Extbase_Persistence_QOM_UpperCaseInterface')) {
    return;
}
interface Tx_Extbase_Persistence_QOM_UpperCaseInterface
{
}
\class_alias('Tx_Extbase_Persistence_QOM_UpperCaseInterface', 'Tx_Extbase_Persistence_QOM_UpperCaseInterface', \false);
