<?php

namespace RectorPrefix20210829;

if (\class_exists('Tx_Extbase_MVC_Exception_InvalidRequestType')) {
    return;
}
class Tx_Extbase_MVC_Exception_InvalidRequestType
{
}
\class_alias('Tx_Extbase_MVC_Exception_InvalidRequestType', 'Tx_Extbase_MVC_Exception_InvalidRequestType', \false);
