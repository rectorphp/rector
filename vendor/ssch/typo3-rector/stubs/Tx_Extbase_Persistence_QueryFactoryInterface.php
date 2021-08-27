<?php

namespace RectorPrefix20210827;

if (\interface_exists('Tx_Extbase_Persistence_QueryFactoryInterface')) {
    return;
}
interface Tx_Extbase_Persistence_QueryFactoryInterface
{
}
\class_alias('Tx_Extbase_Persistence_QueryFactoryInterface', 'Tx_Extbase_Persistence_QueryFactoryInterface', \false);
