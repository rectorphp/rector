<?php

namespace RectorPrefix20210901;

if (\class_exists('Tx_Extbase_MVC_Exception_InvalidUriPattern')) {
    return;
}
class Tx_Extbase_MVC_Exception_InvalidUriPattern
{
}
\class_alias('Tx_Extbase_MVC_Exception_InvalidUriPattern', 'Tx_Extbase_MVC_Exception_InvalidUriPattern', \false);
