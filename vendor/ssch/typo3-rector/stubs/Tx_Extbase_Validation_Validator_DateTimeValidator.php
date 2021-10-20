<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Validation_Validator_DateTimeValidator')) {
    return;
}
class Tx_Extbase_Validation_Validator_DateTimeValidator
{
}
\class_alias('Tx_Extbase_Validation_Validator_DateTimeValidator', 'Tx_Extbase_Validation_Validator_DateTimeValidator', \false);
