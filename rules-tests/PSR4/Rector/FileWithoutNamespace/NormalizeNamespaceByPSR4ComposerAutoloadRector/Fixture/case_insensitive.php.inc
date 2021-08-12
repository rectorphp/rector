<?php

declare(strict_types=1);

namespace Rector\TestS\PSR4\RecTor\fileWithoutNamespace\NorMAliZeNamespaceByPSR4ComposerAutoloadRector\fixture;

class Foo
{

}
?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Fixture;

class Foo
{

}
?>
