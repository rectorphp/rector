<?php

namespace RectorPrefix20210916;

if (\class_exists('Tx_Extbase_MVC_Exception_InvalidArgumentType')) {
    return;
}
class Tx_Extbase_MVC_Exception_InvalidArgumentType
{
}
\class_alias('Tx_Extbase_MVC_Exception_InvalidArgumentType', 'Tx_Extbase_MVC_Exception_InvalidArgumentType', \false);
