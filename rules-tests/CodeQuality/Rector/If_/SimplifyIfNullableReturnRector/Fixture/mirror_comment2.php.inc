<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class MirrorComment2
{
    public function run()
    {
        /**
         * a comment
         *
         * @var \stdClass|null $value
         */
        $value = $this->bar();
        if (! $value instanceof \stdClass) {
            return null;
        }

        return $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class MirrorComment2
{
    public function run()
    {
        /**
         * a comment
         */
        return $this->bar();
    }
}

?>
