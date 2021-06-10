<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

use Rector\Core\Contract\Rector\RectorInterface;

final class SomeStaticPropertyFetch
{
    private $rector;
    public function __construct(
        RectorInterface $rector
    ) {
        self::$rector = $rector;
    }

    /**
     * @return class-string<RectorInterface>
     */
    public function getRectorClass(): string
    {
        return self::$rector::class;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

use Rector\Core\Contract\Rector\RectorInterface;

final class SomeStaticPropertyFetch
{
    private $rector;
    public function __construct(
        RectorInterface $rector
    ) {
        self::$rector = $rector;
    }

    /**
     * @return class-string<RectorInterface>
     */
    public function getRectorClass(): string
    {
        return get_class(self::$rector);
    }
}

?>
