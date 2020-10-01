<?php

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

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

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Fixture;

class ReturnTagType
{
    /**
     * @return \Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\Source\ShortClassOnly
     */
    public function get($shortClassOnly)
    {
    }
}

?>
