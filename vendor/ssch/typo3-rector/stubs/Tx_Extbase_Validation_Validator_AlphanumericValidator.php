<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Validation_Validator_AlphanumericValidator')) {
    return;
}
class Tx_Extbase_Validation_Validator_AlphanumericValidator
{
}
\class_alias('Tx_Extbase_Validation_Validator_AlphanumericValidator', 'Tx_Extbase_Validation_Validator_AlphanumericValidator', \false);
