<?php

namespace RectorPrefix20210916;

if (\class_exists('Tx_Extbase_Validation_Validator_NumberRangeValidator')) {
    return;
}
class Tx_Extbase_Validation_Validator_NumberRangeValidator
{
}
\class_alias('Tx_Extbase_Validation_Validator_NumberRangeValidator', 'Tx_Extbase_Validation_Validator_NumberRangeValidator', \false);
