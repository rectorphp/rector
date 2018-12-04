<?php

namespace Rector\Php\Tests\Rector\ClassConst\PublicConstantVisibilityRector\Wrong;

final class SomeClass
{
    const VALUE = 'hey';

    public const ALREADY = 'yep';

    private const HEARTH = 'bum';
}

?>
    -----
<?php

namespace Rector\Php\Tests\Rector\ClassConst\PublicConstantVisibilityRector\Wrong;

final class SomeClass
{
    public const VALUE = 'hey';

    public const ALREADY = 'yep';

    private const HEARTH = 'bum';
}

?>
