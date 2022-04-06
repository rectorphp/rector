<?php

namespace
{
    echo 'test';
}

namespace App
{
    class HasPrevNamespace
    {
    }
}

?>
-----
<?php

namespace
{
    echo 'test';
}

namespace {
    class HasPrevNamespace
    {
    }
}

?>
