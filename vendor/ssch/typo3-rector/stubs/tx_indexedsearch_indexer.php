<?php

namespace RectorPrefix20211020;

if (\class_exists('tx_indexedsearch_indexer')) {
    return;
}
class tx_indexedsearch_indexer
{
}
\class_alias('tx_indexedsearch_indexer', 'tx_indexedsearch_indexer', \false);
