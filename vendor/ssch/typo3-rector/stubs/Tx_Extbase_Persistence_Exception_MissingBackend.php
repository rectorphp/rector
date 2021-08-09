<?php

namespace RectorPrefix20210809;

if (\class_exists('Tx_Extbase_Persistence_Exception_MissingBackend')) {
    return;
}
class Tx_Extbase_Persistence_Exception_MissingBackend
{
}
\class_alias('Tx_Extbase_Persistence_Exception_MissingBackend', 'Tx_Extbase_Persistence_Exception_MissingBackend', \false);
