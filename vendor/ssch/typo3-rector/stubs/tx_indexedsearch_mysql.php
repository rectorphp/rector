<?php

namespace RectorPrefix20210707;

if (\class_exists('tx_indexedsearch_mysql')) {
    return;
}
class tx_indexedsearch_mysql
{
}
\class_alias('tx_indexedsearch_mysql', 'tx_indexedsearch_mysql', \false);
