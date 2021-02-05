<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Fixture;

use Exception;

final class SpecialClassNameWithAnonymousClass
{
    public function weird() : void
    {
        $exception = new class() extends Exception {
            public static function create() : self
            {
                $exception = new self();

                return $exception;
            }
        };

        throw $exception;
    }
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Fixture;

use Exception;

final class SpecialClassNameWithAnonymousClass
{
    public function weird() : void
    {
        $exception = new class() extends Exception {
            public static function create() : self
            {
                return new self();
            }
        };

        throw $exception;
    }
}
?>
