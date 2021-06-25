<?php

declare(strict_types=1);

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureRenameParent;

final class WhateverParentIsMissing extends \ThisClassDoesNotExistAnymore
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureRenameParent;

final class WhateverParentIsMissing extends \NewClassThatDoesNotExistEither
{
}

?>
