<?php

namespace App
{
    class HasNextNamespace
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
    class HasNextNamespace
    {
    }
}

namespace
{
    echo 'test';
}

?>
