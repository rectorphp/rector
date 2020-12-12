<?php

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

use ShortClassOnly;

class Fixture
{
}

?>
-----
<?php

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

use Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Source\ShortClassOnly;

class Fixture
{
}

?>
