<?php

namespace RectorPrefix20210605;

if (\interface_exists('Tx_Extbase_MVC_RequestInterface')) {
    return;
}
interface Tx_Extbase_MVC_RequestInterface
{
}
\class_alias('Tx_Extbase_MVC_RequestInterface', 'Tx_Extbase_MVC_RequestInterface', \false);
