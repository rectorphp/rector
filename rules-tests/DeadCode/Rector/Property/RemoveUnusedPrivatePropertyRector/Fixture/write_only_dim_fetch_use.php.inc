<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class WriteOnlyDimFetchUse
{
    /**
     * @var string
     */
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function buildData(): array
    {
        $data[$this->key] = 10000;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class WriteOnlyDimFetchUse
{
    public function __construct()
    {
    }

    public function buildData(): array
    {
    }
}

?>
