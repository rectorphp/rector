<?php

namespace RectorPrefix20210926;

if (\class_exists('tslib_adminPanelHook')) {
    return;
}
class tslib_adminPanelHook
{
}
\class_alias('tslib_adminPanelHook', 'tslib_adminPanelHook', \false);
