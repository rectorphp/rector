<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

        // performance boost
        \SomeNamespace\gc_disable();
    }
}

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;
    function run_me_never()
    {
        // silent deprecations, since we test them
        error_reporting(E_ALL ^ E_DEPRECATED);

        // performance boost
        gc_disable();
    }
}

namespace SomeNamespace
{
    function gc_disable()
    {}
}

?>
