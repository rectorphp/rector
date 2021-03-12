<?php

namespace Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

use ShortClassOnly;

class AnotherClass
{
}

?>
-----
<?php

namespace Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

use Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Source\ShortClassOnly;

class AnotherClass
{
}

?>
