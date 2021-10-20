<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_db_PreparedStatement')) {
    return;
}
class t3lib_db_PreparedStatement
{
}
\class_alias('t3lib_db_PreparedStatement', 't3lib_db_PreparedStatement', \false);
