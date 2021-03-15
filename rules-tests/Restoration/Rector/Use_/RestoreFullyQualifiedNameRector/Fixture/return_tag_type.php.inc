<?php

namespace Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

class ReturnTagType
{
    /**
     * @return \ShortClassOnly
     */
    public function get($shortClassOnly)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

class ReturnTagType
{
    /**
     * @return \Rector\Tests\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector\Source\ShortClassOnly
     */
    public function get($shortClassOnly)
    {
    }
}

?>
