<?php

namespace RectorPrefix20210916;

if (\class_exists('Tx_Extbase_Configuration_Exception_NoSuchFile')) {
    return;
}
class Tx_Extbase_Configuration_Exception_NoSuchFile
{
}
\class_alias('Tx_Extbase_Configuration_Exception_NoSuchFile', 'Tx_Extbase_Configuration_Exception_NoSuchFile', \false);
