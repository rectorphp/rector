<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Nette\Utils\Strings;
use PhpParser\Node\Scalar\String_;

final class InIfSetRename
{
    public function run($serviceType)
    {
        if (Strings::contains($serviceType, '_') && ! Strings::contains($serviceType, '\\')) {
            $getArgumentValue = new String_($serviceType);
        }

        return $getArgumentValue;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Nette\Utils\Strings;
use PhpParser\Node\Scalar\String_;

final class InIfSetRename
{
    public function run($serviceType)
    {
        if (Strings::contains($serviceType, '_') && ! Strings::contains($serviceType, '\\')) {
            $string = new String_($serviceType);
        }

        return $string;
    }
}

?>
