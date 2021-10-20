<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Validation_Exception_NoSuchValidator')) {
    return;
}
class Tx_Extbase_Validation_Exception_NoSuchValidator
{
}
\class_alias('Tx_Extbase_Validation_Exception_NoSuchValidator', 'Tx_Extbase_Validation_Exception_NoSuchValidator', \false);
