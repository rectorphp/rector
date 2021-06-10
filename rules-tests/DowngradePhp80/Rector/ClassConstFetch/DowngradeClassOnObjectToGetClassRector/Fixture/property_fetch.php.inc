<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

use Rector\Core\Contract\Rector\RectorInterface;

final class SomePropertyFetch
{
    public function __construct(
        private RectorInterface $rector
    ) {
    }

    /**
     * @return class-string<RectorInterface>
     */
    public function getRectorClass(): string
    {
        return $this->rector::class;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

use Rector\Core\Contract\Rector\RectorInterface;

final class SomePropertyFetch
{
    public function __construct(
        private RectorInterface $rector
    ) {
    }

    /**
     * @return class-string<RectorInterface>
     */
    public function getRectorClass(): string
    {
        return get_class($this->rector);
    }
}

?>
