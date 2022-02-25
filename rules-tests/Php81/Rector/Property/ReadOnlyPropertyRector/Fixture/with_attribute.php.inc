<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

final class WithAttribute
{
    #[Serializer\Since(Option::SINCE_20211124)]
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

final class WithAttribute
{
    #[Serializer\Since(Option::SINCE_20211124)]
    private readonly string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}

?>
