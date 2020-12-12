<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\SplitGroupedUseImportsRector\Fixture;

use A, B;

class SomeClass
{
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\SplitGroupedUseImportsRector\Fixture;

use A;
use B;
class SomeClass
{
}

?>
