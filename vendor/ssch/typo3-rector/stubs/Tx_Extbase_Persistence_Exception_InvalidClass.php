<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_Persistence_Exception_InvalidClass')) {
    return;
}
class Tx_Extbase_Persistence_Exception_InvalidClass
{
}
\class_alias('Tx_Extbase_Persistence_Exception_InvalidClass', 'Tx_Extbase_Persistence_Exception_InvalidClass', \false);
