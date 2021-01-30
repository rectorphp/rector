<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureFromMethodCall
{
    public function run()
    {
        $self = new self();
        count($self->getData()) === 0;
        0 === count($self->getData());
        count($self->getData()) > 0;
        0 < count($self->getData());
    }

    public function getData(): array
    {
        return [];
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureFromMethodCall
{
    public function run()
    {
        $self = new self();
        $self->getData() === [];
        [] === $self->getData();
        $self->getData() !== [];
        [] !== $self->getData();
    }

    public function getData(): array
    {
        return [];
    }
}

?>
