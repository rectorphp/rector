<?php

namespace RectorPrefix20210916;

if (\class_exists('Tx_Extbase_Validation_Validator_AbstractValidator')) {
    return;
}
class Tx_Extbase_Validation_Validator_AbstractValidator
{
}
\class_alias('Tx_Extbase_Validation_Validator_AbstractValidator', 'Tx_Extbase_Validation_Validator_AbstractValidator', \false);
