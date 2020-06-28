<?php

declare(strict_types=1);

namespace Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Fixture;

final class Basic
{
    /**
     * @var string
     */
    public const VALUE = 'value';

    public function another()
    {
        return $this->someMethod();
    }

    private function someMethod()
    {
        return self::VALUE;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Fixture;

final class Basic
{
    /**
     * @var string
     */
    public const VALUE = 'value';

    public function another()
    {
        return $this->someMethod();
    }
}

?>
