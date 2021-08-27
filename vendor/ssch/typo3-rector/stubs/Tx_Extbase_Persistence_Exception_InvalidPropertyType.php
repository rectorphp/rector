<?php

namespace RectorPrefix20210827;

if (\class_exists('Tx_Extbase_Persistence_Exception_InvalidPropertyType')) {
    return;
}
class Tx_Extbase_Persistence_Exception_InvalidPropertyType
{
}
\class_alias('Tx_Extbase_Persistence_Exception_InvalidPropertyType', 'Tx_Extbase_Persistence_Exception_InvalidPropertyType', \false);
