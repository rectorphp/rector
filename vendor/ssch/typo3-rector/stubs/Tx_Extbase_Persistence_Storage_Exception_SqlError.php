<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Persistence_Storage_Exception_SqlError')) {
    return;
}
class Tx_Extbase_Persistence_Storage_Exception_SqlError
{
}
\class_alias('Tx_Extbase_Persistence_Storage_Exception_SqlError', 'Tx_Extbase_Persistence_Storage_Exception_SqlError', \false);
