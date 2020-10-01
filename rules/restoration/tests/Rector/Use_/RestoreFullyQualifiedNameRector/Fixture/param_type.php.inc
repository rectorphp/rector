<?php

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

class ParamType
{
    public function run(\ShortClassOnly $shortClassOnly)
    {
    }
}

?>
-----
<?php

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

class ParamType
{
    public function run(\Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Source\ShortClassOnly $shortClassOnly)
    {
    }
}

?>
