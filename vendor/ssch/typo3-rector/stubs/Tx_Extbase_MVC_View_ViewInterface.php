<?php

namespace RectorPrefix20210630;

if (\interface_exists('Tx_Extbase_MVC_View_ViewInterface')) {
    return;
}
interface Tx_Extbase_MVC_View_ViewInterface
{
}
\class_alias('Tx_Extbase_MVC_View_ViewInterface', 'Tx_Extbase_MVC_View_ViewInterface', \false);
