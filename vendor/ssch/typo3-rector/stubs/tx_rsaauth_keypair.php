<?php

namespace RectorPrefix20211020;

if (\class_exists('tx_rsaauth_keypair')) {
    return;
}
class tx_rsaauth_keypair
{
}
\class_alias('tx_rsaauth_keypair', 'tx_rsaauth_keypair', \false);
