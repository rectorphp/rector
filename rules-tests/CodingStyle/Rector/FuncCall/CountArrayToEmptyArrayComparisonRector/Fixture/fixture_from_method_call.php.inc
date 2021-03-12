<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

final class FixtureFromMethodCall
{
    public function run()
    {
        count($this->getData()) === 0;
        0 === count($this->getData());
        count($this->getData()) > 0;
        0 < count($this->getData());
    }

    public function getData(): array
    {
        return [];
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

final class FixtureFromMethodCall
{
    public function run()
    {
        $this->getData() === [];
        [] === $this->getData();
        $this->getData() !== [];
        [] !== $this->getData();
    }

    public function getData(): array
    {
        return [];
    }
}

?>
