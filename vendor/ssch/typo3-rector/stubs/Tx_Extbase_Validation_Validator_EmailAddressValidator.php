<?php

namespace RectorPrefix20210603;

if (\class_exists('Tx_Extbase_Validation_Validator_EmailAddressValidator')) {
    return;
}
class Tx_Extbase_Validation_Validator_EmailAddressValidator
{
}
\class_alias('Tx_Extbase_Validation_Validator_EmailAddressValidator', 'Tx_Extbase_Validation_Validator_EmailAddressValidator', \false);
