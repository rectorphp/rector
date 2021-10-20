<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_MVC_Exception_InvalidActionName')) {
    return;
}
class Tx_Extbase_MVC_Exception_InvalidActionName
{
}
\class_alias('Tx_Extbase_MVC_Exception_InvalidActionName', 'Tx_Extbase_MVC_Exception_InvalidActionName', \false);
