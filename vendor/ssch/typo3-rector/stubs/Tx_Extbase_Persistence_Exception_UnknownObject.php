<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_Persistence_Exception_UnknownObject')) {
    return;
}
class Tx_Extbase_Persistence_Exception_UnknownObject
{
}
\class_alias('Tx_Extbase_Persistence_Exception_UnknownObject', 'Tx_Extbase_Persistence_Exception_UnknownObject', \false);
