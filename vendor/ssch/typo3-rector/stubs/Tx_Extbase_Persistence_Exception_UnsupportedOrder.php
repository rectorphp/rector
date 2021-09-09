<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_Persistence_Exception_UnsupportedOrder')) {
    return;
}
class Tx_Extbase_Persistence_Exception_UnsupportedOrder
{
}
\class_alias('Tx_Extbase_Persistence_Exception_UnsupportedOrder', 'Tx_Extbase_Persistence_Exception_UnsupportedOrder', \false);
