<?php

namespace RectorPrefix20210630;

if (\interface_exists('Tx_Extbase_Persistence_QueryResultInterface')) {
    return;
}
interface Tx_Extbase_Persistence_QueryResultInterface
{
}
\class_alias('Tx_Extbase_Persistence_QueryResultInterface', 'Tx_Extbase_Persistence_QueryResultInterface', \false);
