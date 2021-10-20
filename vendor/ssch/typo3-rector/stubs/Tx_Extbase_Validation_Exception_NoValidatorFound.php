<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Validation_Exception_NoValidatorFound')) {
    return;
}
class Tx_Extbase_Validation_Exception_NoValidatorFound
{
}
\class_alias('Tx_Extbase_Validation_Exception_NoValidatorFound', 'Tx_Extbase_Validation_Exception_NoValidatorFound', \false);
