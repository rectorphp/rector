<?php

namespace RectorPrefix20210922;

if (\class_exists('Tx_Extbase_MVC_Exception_StopAction')) {
    return;
}
class Tx_Extbase_MVC_Exception_StopAction
{
}
\class_alias('Tx_Extbase_MVC_Exception_StopAction', 'Tx_Extbase_MVC_Exception_StopAction', \false);
