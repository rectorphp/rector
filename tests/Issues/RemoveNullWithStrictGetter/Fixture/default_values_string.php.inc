<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\RemoveNullWithStrictGetter\Fixture;

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

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\RemoveNullWithStrictGetter\Fixture;

final class DefaultValuesString
{
    private string $name = 'not_a_bool';

    public function getName(): string
    {
        return $this->name;
    }
}

?>
