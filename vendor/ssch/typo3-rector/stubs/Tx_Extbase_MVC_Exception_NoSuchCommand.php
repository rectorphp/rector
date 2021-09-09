<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_MVC_Exception_NoSuchCommand')) {
    return;
}
class Tx_Extbase_MVC_Exception_NoSuchCommand
{
}
\class_alias('Tx_Extbase_MVC_Exception_NoSuchCommand', 'Tx_Extbase_MVC_Exception_NoSuchCommand', \false);
