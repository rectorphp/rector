<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Validation_Exception')) {
    return;
}
class Tx_Extbase_Validation_Exception
{
}
\class_alias('Tx_Extbase_Validation_Exception', 'Tx_Extbase_Validation_Exception', \false);
