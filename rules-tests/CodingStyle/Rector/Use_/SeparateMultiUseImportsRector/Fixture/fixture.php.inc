<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector\Fixture;

use A, B;

class SomeClass
{
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector\Fixture;

use A;
use B;
class SomeClass
{
}

?>
