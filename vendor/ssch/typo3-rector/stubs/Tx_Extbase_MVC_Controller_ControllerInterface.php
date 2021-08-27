<?php

namespace RectorPrefix20210827;

if (\interface_exists('Tx_Extbase_MVC_Controller_ControllerInterface')) {
    return;
}
interface Tx_Extbase_MVC_Controller_ControllerInterface
{
}
\class_alias('Tx_Extbase_MVC_Controller_ControllerInterface', 'Tx_Extbase_MVC_Controller_ControllerInterface', \false);
