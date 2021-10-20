<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Persistence_LazyLoadingProxy')) {
    return;
}
class Tx_Extbase_Persistence_LazyLoadingProxy
{
}
\class_alias('Tx_Extbase_Persistence_LazyLoadingProxy', 'Tx_Extbase_Persistence_LazyLoadingProxy', \false);
