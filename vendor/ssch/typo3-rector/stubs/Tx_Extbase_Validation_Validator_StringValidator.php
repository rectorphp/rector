<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Validation_Validator_StringValidator')) {
    return;
}
class Tx_Extbase_Validation_Validator_StringValidator
{
}
\class_alias('Tx_Extbase_Validation_Validator_StringValidator', 'Tx_Extbase_Validation_Validator_StringValidator', \false);
