<?php

namespace RectorPrefix20211010;

if (\class_exists('tx_install_session')) {
    return;
}
class tx_install_session
{
}
\class_alias('tx_install_session', 'tx_install_session', \false);
