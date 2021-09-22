<?php

namespace RectorPrefix20210922;

if (\interface_exists('Tx_Extbase_MVC_ResponseInterface')) {
    return;
}
interface Tx_Extbase_MVC_ResponseInterface
{
}
\class_alias('Tx_Extbase_MVC_ResponseInterface', 'Tx_Extbase_MVC_ResponseInterface', \false);
