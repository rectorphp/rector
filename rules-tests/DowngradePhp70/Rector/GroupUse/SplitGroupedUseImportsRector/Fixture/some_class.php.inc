<?php

namespace Rector\Tests\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector\Fixture;

use SomeNamespace\{
    First,
    Second
};

class SomeClass
{
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector\Fixture;

use SomeNamespace\First;
use SomeNamespace\Second;
class SomeClass
{
}

?>
