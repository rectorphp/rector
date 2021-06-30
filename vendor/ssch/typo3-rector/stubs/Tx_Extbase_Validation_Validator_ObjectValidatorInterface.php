<?php

namespace RectorPrefix20210630;

if (\interface_exists('Tx_Extbase_Validation_Validator_ObjectValidatorInterface')) {
    return;
}
interface Tx_Extbase_Validation_Validator_ObjectValidatorInterface
{
}
\class_alias('Tx_Extbase_Validation_Validator_ObjectValidatorInterface', 'Tx_Extbase_Validation_Validator_ObjectValidatorInterface', \false);
