<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

final class Anything
{
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use TYPO3\CMS\Extbase\Annotation as Extbase;
final class Anything
{
    public function go()
    {
    }
}

?>
