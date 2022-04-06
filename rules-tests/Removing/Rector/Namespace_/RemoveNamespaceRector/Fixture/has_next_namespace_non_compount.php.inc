<?php

namespace App
{
    use stdClass;
    class HasNextNamespaceNonCompount
    {
    }
}

namespace
{
    echo 'test';
}

?>
-----
<?php

namespace {
    class HasNextNamespaceNonCompount
    {
    }
}

namespace
{
    echo 'test';
}

?>
