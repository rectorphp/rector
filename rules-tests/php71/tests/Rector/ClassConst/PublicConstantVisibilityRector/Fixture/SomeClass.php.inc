<?php

namespace Rector\Php71\Tests\Rector\ClassConst\PublicConstantVisibilityRector\Fixture;

final class Someclass
{
    const VALUE = 'hey';

    public const ALREADY = 'yep';

    private const HEARTH = 'bum';
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\ClassConst\PublicConstantVisibilityRector\Fixture;

final class Someclass
{
    public const VALUE = 'hey';

    public const ALREADY = 'yep';

    private const HEARTH = 'bum';
}

?>
