<?php

namespace RectorPrefix20210827;

if (\interface_exists('Tx_Extbase_Persistence_BackendInterface')) {
    return;
}
interface Tx_Extbase_Persistence_BackendInterface
{
}
\class_alias('Tx_Extbase_Persistence_BackendInterface', 'Tx_Extbase_Persistence_BackendInterface', \false);
