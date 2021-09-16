<?php

namespace RectorPrefix20210916;

if (\class_exists('Tx_Extbase_Configuration_BackendConfigurationManager')) {
    return;
}
class Tx_Extbase_Configuration_BackendConfigurationManager
{
}
\class_alias('Tx_Extbase_Configuration_BackendConfigurationManager', 'Tx_Extbase_Configuration_BackendConfigurationManager', \false);
