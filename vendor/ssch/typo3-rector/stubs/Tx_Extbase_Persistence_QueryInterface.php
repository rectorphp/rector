<?php

namespace RectorPrefix20210902;

if (\interface_exists('Tx_Extbase_Persistence_QueryInterface')) {
    return;
}
interface Tx_Extbase_Persistence_QueryInterface
{
}
\class_alias('Tx_Extbase_Persistence_QueryInterface', 'Tx_Extbase_Persistence_QueryInterface', \false);
