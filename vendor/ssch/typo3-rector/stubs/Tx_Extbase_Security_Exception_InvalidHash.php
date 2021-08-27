<?php

namespace RectorPrefix20210827;

if (\class_exists('Tx_Extbase_Security_Exception_InvalidHash')) {
    return;
}
class Tx_Extbase_Security_Exception_InvalidHash
{
}
\class_alias('Tx_Extbase_Security_Exception_InvalidHash', 'Tx_Extbase_Security_Exception_InvalidHash', \false);
