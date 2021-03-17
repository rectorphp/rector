<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesString
{
    /**
     * @var bool
     */
    private $name = 'not_a_bool';

    public function getName(): string
    {
        return $this->name;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesString
{
    private string $name = 'not_a_bool';

    public function getName(): string
    {
        return $this->name;
    }
}

?>
