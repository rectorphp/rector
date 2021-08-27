<?php

namespace RectorPrefix20210827;

if (\class_exists('Tx_Extbase_MVC_Exception_InvalidRequestMethod')) {
    return;
}
class Tx_Extbase_MVC_Exception_InvalidRequestMethod
{
}
\class_alias('Tx_Extbase_MVC_Exception_InvalidRequestMethod', 'Tx_Extbase_MVC_Exception_InvalidRequestMethod', \false);
