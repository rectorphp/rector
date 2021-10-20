<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Validation_Exception_InvalidSubject')) {
    return;
}
class Tx_Extbase_Validation_Exception_InvalidSubject
{
}
\class_alias('Tx_Extbase_Validation_Exception_InvalidSubject', 'Tx_Extbase_Validation_Exception_InvalidSubject', \false);
