<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\StringableTypedProperty\Fixture;

class Fixture
{
    private $nickname;

    public function __toString()
    {
        return $this->nickname;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\StringableTypedProperty\Fixture;

class Fixture implements \Stringable
{
    private ?string $nickname = null;

    public function __toString(): string
    {
        return (string) $this->nickname;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }
}

?>
