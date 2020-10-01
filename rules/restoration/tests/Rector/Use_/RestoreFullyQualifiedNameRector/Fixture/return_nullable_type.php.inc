<?php

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

class ReturnNullableType
{
    public function get($shortClassOnly): ?\ShortClassOnly
    {
    }
}

?>
-----
<?php

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

class ReturnNullableType
{
    public function get($shortClassOnly): ?\Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Source\ShortClassOnly
    {
    }
}

?>
