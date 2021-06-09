<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotAddDefaultValueFilledByConstruct
{
    private $result;

    public function __construct(?array $result) {
        $this->result = $result;
    }

    public function getResult(): ?array
    {
        return $this->result;
    }
}
?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotAddDefaultValueFilledByConstruct
{
    private ?array $result;

    public function __construct(?array $result) {
        $this->result = $result;
    }

    public function getResult(): ?array
    {
        return $this->result;
    }
}
?>
