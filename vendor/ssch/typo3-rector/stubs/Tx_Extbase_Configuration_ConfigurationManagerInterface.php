<?php

namespace RectorPrefix20210827;

if (\interface_exists('Tx_Extbase_Configuration_ConfigurationManagerInterface')) {
    return;
}
interface Tx_Extbase_Configuration_ConfigurationManagerInterface
{
}
\class_alias('Tx_Extbase_Configuration_ConfigurationManagerInterface', 'Tx_Extbase_Configuration_ConfigurationManagerInterface', \false);
