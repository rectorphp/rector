<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Persistence_Storage_Exception_BadConstraint')) {
    return;
}
class Tx_Extbase_Persistence_Storage_Exception_BadConstraint
{
}
\class_alias('Tx_Extbase_Persistence_Storage_Exception_BadConstraint', 'Tx_Extbase_Persistence_Storage_Exception_BadConstraint', \false);
