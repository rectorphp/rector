<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_Persistence_LazyLoadingProxy')) {
    return;
}
class Tx_Extbase_Persistence_LazyLoadingProxy
{
}
\class_alias('Tx_Extbase_Persistence_LazyLoadingProxy', 'Tx_Extbase_Persistence_LazyLoadingProxy', \false);
