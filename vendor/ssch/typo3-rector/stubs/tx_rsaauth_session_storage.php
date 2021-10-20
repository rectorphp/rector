<?php

namespace RectorPrefix20211020;

if (\class_exists('tx_rsaauth_session_storage')) {
    return;
}
class tx_rsaauth_session_storage
{
}
\class_alias('tx_rsaauth_session_storage', 'tx_rsaauth_session_storage', \false);
