<?php

namespace RectorPrefix20210910;

if (\interface_exists('Tx_Extbase_Persistence_LoadingStrategyInterface')) {
    return;
}
interface Tx_Extbase_Persistence_LoadingStrategyInterface
{
}
\class_alias('Tx_Extbase_Persistence_LoadingStrategyInterface', 'Tx_Extbase_Persistence_LoadingStrategyInterface', \false);
