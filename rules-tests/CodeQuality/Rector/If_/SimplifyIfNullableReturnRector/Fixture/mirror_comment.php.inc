<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class MirrorComment
{
    private function bar(): ?\stdClass
    {
        if (rand(0, 1)) {
            return new \stdClass();
        }

        return null;
    }

    public function run()
    {
        // a comment
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

final class MirrorComment
{
    private function bar(): ?\stdClass
    {
        if (rand(0, 1)) {
            return new \stdClass();
        }

        return null;
    }

    public function run()
    {
        // a comment
        return $this->bar();
    }
}

?>
