<?php

namespace RectorPrefix20211108;

if (\class_exists('Tx_Extbase_Persistence_Exception_TooDirty')) {
    return;
}
class Tx_Extbase_Persistence_Exception_TooDirty
{
}
\class_alias('Tx_Extbase_Persistence_Exception_TooDirty', 'Tx_Extbase_Persistence_Exception_TooDirty', \false);
