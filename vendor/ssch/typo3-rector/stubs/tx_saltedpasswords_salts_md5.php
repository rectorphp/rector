<?php

namespace RectorPrefix20211017;

if (\class_exists('tx_saltedpasswords_salts_md5')) {
    return;
}
class tx_saltedpasswords_salts_md5
{
}
\class_alias('tx_saltedpasswords_salts_md5', 'tx_saltedpasswords_salts_md5', \false);
