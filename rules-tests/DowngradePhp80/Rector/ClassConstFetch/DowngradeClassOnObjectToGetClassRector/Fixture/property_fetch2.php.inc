<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

use Rector\Core\Contract\Rector\RectorInterface;

final class SomePropertyFetch2
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
        $expr = new \stdClass;
        $expr->var = $this->rector;
        return $expr->var::class;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

use Rector\Core\Contract\Rector\RectorInterface;

final class SomePropertyFetch2
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
        $expr = new \stdClass;
        $expr->var = $this->rector;
        return get_class($expr->var);
    }
}

?>
