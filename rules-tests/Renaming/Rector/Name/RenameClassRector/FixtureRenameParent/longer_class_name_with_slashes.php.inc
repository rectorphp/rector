<?php

declare(strict_types=1);

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureRenameParent;

use App\NotHereClass\AndNamespace;

final class LongerClassNameWithSlashes extends AndNamespace
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureRenameParent;

use App\NotHereClass\AndNamespace;

final class LongerClassNameWithSlashes extends \NewClassThatDoesNotExistEither
{
}

?>
