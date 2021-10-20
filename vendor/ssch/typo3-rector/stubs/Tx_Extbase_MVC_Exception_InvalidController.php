<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_MVC_Exception_InvalidController')) {
    return;
}
class Tx_Extbase_MVC_Exception_InvalidController
{
}
\class_alias('Tx_Extbase_MVC_Exception_InvalidController', 'Tx_Extbase_MVC_Exception_InvalidController', \false);
