<?php

namespace RectorPrefix20210630;

if (\interface_exists('Tx_Extbase_MVC_Controller_CommandControllerInterface')) {
    return;
}
interface Tx_Extbase_MVC_Controller_CommandControllerInterface
{
}
\class_alias('Tx_Extbase_MVC_Controller_CommandControllerInterface', 'Tx_Extbase_MVC_Controller_CommandControllerInterface', \false);
