<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\RemoveNullWithStrictGetter\Fixture;

final class AddNull
{
    private $name;

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\RemoveNullWithStrictGetter\Fixture;

final class AddNull
{
    private ?string $name = null;

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}

?>
