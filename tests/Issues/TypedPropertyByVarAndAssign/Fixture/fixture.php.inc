<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\TypedPropertyByVarAndAssign\Fixture;

class Fixture
{
    /**
     * @var string|false|null
     */
    private $property;

    public function run()
    {
        $this->property = '';
    }

    public function run2()
    {
        $this->property = true;
    }

    public function run3()
    {
        $this->property = null;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\TypedPropertyByVarAndAssign\Fixture;

class Fixture
{
    /**
     * @var string|false|null
     */
    private bool|string|null $property = null;

    public function run()
    {
        $this->property = '';
    }

    public function run2()
    {
        $this->property = true;
    }

    public function run3()
    {
        $this->property = null;
    }
}

?>
