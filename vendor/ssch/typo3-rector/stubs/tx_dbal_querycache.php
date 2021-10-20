<?php

namespace RectorPrefix20211020;

if (\class_exists('tx_dbal_querycache')) {
    return;
}
class tx_dbal_querycache
{
}
\class_alias('tx_dbal_querycache', 'tx_dbal_querycache', \false);
