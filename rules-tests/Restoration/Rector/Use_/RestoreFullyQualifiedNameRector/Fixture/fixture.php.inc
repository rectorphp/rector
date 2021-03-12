<?php

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

use ShortClassOnly;

class AnotherClass
{
}

?>
-----
<?php

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

use Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Source\ShortClassOnly;

class AnotherClass
{
}

?>
