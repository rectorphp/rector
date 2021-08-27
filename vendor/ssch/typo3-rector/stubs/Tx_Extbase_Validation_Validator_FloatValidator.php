<?php

namespace RectorPrefix20210827;

if (\class_exists('Tx_Extbase_Validation_Validator_FloatValidator')) {
    return;
}
class Tx_Extbase_Validation_Validator_FloatValidator
{
}
\class_alias('Tx_Extbase_Validation_Validator_FloatValidator', 'Tx_Extbase_Validation_Validator_FloatValidator', \false);
