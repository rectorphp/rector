<?php

namespace Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

class ParamType
{
    public function run(\ShortClassOnly $shortClassOnly)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

class ParamType
{
    public function run(\Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Source\ShortClassOnly $shortClassOnly)
    {
    }
}

?>
