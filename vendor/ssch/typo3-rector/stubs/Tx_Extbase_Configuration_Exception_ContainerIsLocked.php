<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Configuration_Exception_ContainerIsLocked')) {
    return;
}
class Tx_Extbase_Configuration_Exception_ContainerIsLocked
{
}
\class_alias('Tx_Extbase_Configuration_Exception_ContainerIsLocked', 'Tx_Extbase_Configuration_Exception_ContainerIsLocked', \false);
