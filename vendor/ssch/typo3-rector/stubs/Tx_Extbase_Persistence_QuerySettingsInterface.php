<?php

namespace RectorPrefix20210708;

if (\interface_exists('Tx_Extbase_Persistence_QuerySettingsInterface')) {
    return;
}
interface Tx_Extbase_Persistence_QuerySettingsInterface
{
}
\class_alias('Tx_Extbase_Persistence_QuerySettingsInterface', 'Tx_Extbase_Persistence_QuerySettingsInterface', \false);
