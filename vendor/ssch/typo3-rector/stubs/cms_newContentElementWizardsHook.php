<?php

namespace RectorPrefix20210901;

if (\class_exists('cms_newContentElementWizardsHook')) {
    return;
}
class cms_newContentElementWizardsHook
{
}
\class_alias('cms_newContentElementWizardsHook', 'cms_newContentElementWizardsHook', \false);
