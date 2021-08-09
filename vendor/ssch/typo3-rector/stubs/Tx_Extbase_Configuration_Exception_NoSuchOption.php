<?php

namespace RectorPrefix20210809;

if (\class_exists('Tx_Extbase_Configuration_Exception_NoSuchOption')) {
    return;
}
class Tx_Extbase_Configuration_Exception_NoSuchOption
{
}
\class_alias('Tx_Extbase_Configuration_Exception_NoSuchOption', 'Tx_Extbase_Configuration_Exception_NoSuchOption', \false);
