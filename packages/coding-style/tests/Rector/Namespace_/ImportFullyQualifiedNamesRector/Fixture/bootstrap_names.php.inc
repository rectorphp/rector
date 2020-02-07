<?php

declare(strict_types=1);

function run_me_never()
{
    // silent deprecations, since we test them
    error_reporting(E_ALL ^ E_DEPRECATED);

    // performance boost
    \SomeNamespace\gc_disable();
}

?>
-----
<?php

declare(strict_types=1);
use function SomeNamespace\gc_disable;

function run_me_never()
{
    // silent deprecations, since we test them
    error_reporting(E_ALL ^ E_DEPRECATED);

    // performance boost
    gc_disable();
}

?>
