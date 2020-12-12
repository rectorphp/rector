<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\SplitGroupedUseImportsRector\Fixture;

use A, B;

class Fixture
{
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\SplitGroupedUseImportsRector\Fixture;

use A;
use B;
class Fixture
{
}

?>
