<?php

namespace RectorPrefix20210627;

if (\class_exists('Tx_Extbase_MVC_Exception_NoSuchCommand')) {
    return;
}
class Tx_Extbase_MVC_Exception_NoSuchCommand
{
}
\class_alias('Tx_Extbase_MVC_Exception_NoSuchCommand', 'Tx_Extbase_MVC_Exception_NoSuchCommand', \false);
