<?php

namespace RectorPrefix20211010;

if (\class_exists('tx_rsaauth_abstract_backend')) {
    return;
}
class tx_rsaauth_abstract_backend
{
}
\class_alias('tx_rsaauth_abstract_backend', 'tx_rsaauth_abstract_backend', \false);
