<?php

namespace RectorPrefix20210809;

if (\class_exists('Tx_Extbase_Security_Exception')) {
    return;
}
class Tx_Extbase_Security_Exception
{
}
\class_alias('Tx_Extbase_Security_Exception', 'Tx_Extbase_Security_Exception', \false);
