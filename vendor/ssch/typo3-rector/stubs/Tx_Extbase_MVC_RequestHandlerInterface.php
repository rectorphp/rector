<?php

namespace RectorPrefix20210827;

if (\interface_exists('Tx_Extbase_MVC_RequestHandlerInterface')) {
    return;
}
interface Tx_Extbase_MVC_RequestHandlerInterface
{
}
\class_alias('Tx_Extbase_MVC_RequestHandlerInterface', 'Tx_Extbase_MVC_RequestHandlerInterface', \false);
