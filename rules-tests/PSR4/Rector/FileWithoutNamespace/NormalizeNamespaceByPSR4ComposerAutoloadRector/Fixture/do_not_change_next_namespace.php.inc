<?php

namespace App {
    function execute()
    {
        echo 'execute from named namespace';
    }
}

namespace
{
    function execute()
    {
        echo 'execute from non-named namespace';
    }
}

?>
-----
<?php

namespace Rector\Tests\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Fixture {
    function execute()
    {
        echo 'execute from named namespace';
    }
}

namespace
{
    function execute()
    {
        echo 'execute from non-named namespace';
    }
}

?>
