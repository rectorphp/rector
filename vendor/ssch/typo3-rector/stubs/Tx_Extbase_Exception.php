<?php

namespace RectorPrefix20211003;

if (\class_exists('Tx_Extbase_Exception')) {
    return;
}
class Tx_Extbase_Exception
{
}
\class_alias('Tx_Extbase_Exception', 'Tx_Extbase_Exception', \false);
