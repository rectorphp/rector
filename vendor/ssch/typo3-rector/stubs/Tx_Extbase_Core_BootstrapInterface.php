<?php

namespace RectorPrefix20210827;

if (\interface_exists('Tx_Extbase_Core_BootstrapInterface')) {
    return;
}
interface Tx_Extbase_Core_BootstrapInterface
{
}
\class_alias('Tx_Extbase_Core_BootstrapInterface', 'Tx_Extbase_Core_BootstrapInterface', \false);
