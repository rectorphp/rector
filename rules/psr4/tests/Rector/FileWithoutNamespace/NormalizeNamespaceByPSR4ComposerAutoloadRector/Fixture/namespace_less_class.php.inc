<?php

declare(strict_types=1);

class NamespaceLessClass
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Fixture;

class NamespaceLessClass
{
}

?>
