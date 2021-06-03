<?php

namespace RectorPrefix20210603;

if (\class_exists('Tx_Extbase_Security_Exception_InvalidArgumentForHashGeneration')) {
    return;
}
class Tx_Extbase_Security_Exception_InvalidArgumentForHashGeneration
{
}
\class_alias('Tx_Extbase_Security_Exception_InvalidArgumentForHashGeneration', 'Tx_Extbase_Security_Exception_InvalidArgumentForHashGeneration', \false);
