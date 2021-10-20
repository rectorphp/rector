<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_MVC_Exception_AmbiguousCommandIdentifier')) {
    return;
}
class Tx_Extbase_MVC_Exception_AmbiguousCommandIdentifier
{
}
\class_alias('Tx_Extbase_MVC_Exception_AmbiguousCommandIdentifier', 'Tx_Extbase_MVC_Exception_AmbiguousCommandIdentifier', \false);
