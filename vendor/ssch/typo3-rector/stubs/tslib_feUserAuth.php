<?php

namespace RectorPrefix20211020;

if (\class_exists('tslib_feUserAuth')) {
    return;
}
class tslib_feUserAuth
{
}
\class_alias('tslib_feUserAuth', 'tslib_feUserAuth', \false);
