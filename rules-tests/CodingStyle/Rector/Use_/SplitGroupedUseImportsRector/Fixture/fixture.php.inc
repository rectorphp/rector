<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector\Fixture;

use A, B;

class SomeClass
{
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector\Fixture;

use A;
use B;
class SomeClass
{
}

?>
