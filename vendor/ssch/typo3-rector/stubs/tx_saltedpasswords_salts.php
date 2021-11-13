<?php

namespace RectorPrefix20211113;

if (\class_exists('tx_saltedpasswords_salts')) {
    return;
}
class tx_saltedpasswords_salts
{
}
\class_alias('tx_saltedpasswords_salts', 'tx_saltedpasswords_salts', \false);
