<?php

namespace RectorPrefix20210827;

if (\class_exists('Tx_Extbase_MVC_Exception_Command')) {
    return;
}
class Tx_Extbase_MVC_Exception_Command
{
}
\class_alias('Tx_Extbase_MVC_Exception_Command', 'Tx_Extbase_MVC_Exception_Command', \false);
