<?php

namespace RectorPrefix20211019;

if (\class_exists('Tx_Extbase_Security_Exception_InvalidArgumentForHashGeneration')) {
    return;
}
class Tx_Extbase_Security_Exception_InvalidArgumentForHashGeneration
{
}
\class_alias('Tx_Extbase_Security_Exception_InvalidArgumentForHashGeneration', 'Tx_Extbase_Security_Exception_InvalidArgumentForHashGeneration', \false);
