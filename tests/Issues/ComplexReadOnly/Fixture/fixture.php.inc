<?php

namespace Rector\Core\Tests\Issues\ComplexReadOnly\Fixture;

final class Fixture
{
    public function __construct(
        private array|null $condExprs
    ) {
    }

    public function getCondExprs(): array|null
    {
        if ($this->condExprs === []) {
            return null;
        }

        return $this->condExprs;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\ComplexReadOnly\Fixture;

final class Fixture
{
    public function __construct(
        private readonly array|null $condExprs
    ) {
    }

    public function getCondExprs(): array|null
    {
        if ($this->condExprs === []) {
            return null;
        }

        return $this->condExprs;
    }
}

?>
