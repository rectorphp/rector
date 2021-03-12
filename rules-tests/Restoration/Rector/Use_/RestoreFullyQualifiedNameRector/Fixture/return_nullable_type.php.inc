<?php

namespace Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

class ReturnNullableType
{
    public function get($shortClassOnly): ?\ShortClassOnly
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

class ReturnNullableType
{
    public function get($shortClassOnly): ?\Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Source\ShortClassOnly
    {
    }
}

?>
