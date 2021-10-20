<?php

namespace RectorPrefix20211020;

if (\class_exists('tx_rsaauth_abstract_storage')) {
    return;
}
class tx_rsaauth_abstract_storage
{
}
\class_alias('tx_rsaauth_abstract_storage', 'tx_rsaauth_abstract_storage', \false);
