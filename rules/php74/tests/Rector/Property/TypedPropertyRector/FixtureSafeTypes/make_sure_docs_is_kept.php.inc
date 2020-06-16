<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureSafeTypes;

final class MakeSureDocsIsKept
{
    private $name;

    /**
     * @var string
     */
    private $surname;

    /**
     * @param string $surname
     */
    public function __construct(string $name, $surname)
    {
        $this->name = $name;
        $this->surname = $surname;
    }
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureSafeTypes;

final class MakeSureDocsIsKept
{
    private string $name;

    /**
     * @var string
     */
    private $surname;

    /**
     * @param string $surname
     */
    public function __construct(string $name, $surname)
    {
        $this->name = $name;
        $this->surname = $surname;
    }
}

?>
