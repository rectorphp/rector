<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Workspaces_Service_AutoPublishTask')) {
    return;
}
class Tx_Workspaces_Service_AutoPublishTask
{
}
\class_alias('Tx_Workspaces_Service_AutoPublishTask', 'Tx_Workspaces_Service_AutoPublishTask', \false);
