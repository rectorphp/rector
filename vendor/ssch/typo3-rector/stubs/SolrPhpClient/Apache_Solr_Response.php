<?php

namespace RectorPrefix20210617;

if (\class_exists('Apache_Solr_Response')) {
    return;
}
class Apache_Solr_Response
{
}
\class_alias('Apache_Solr_Response', 'Apache_Solr_Response', \false);
