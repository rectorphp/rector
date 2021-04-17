<?php

final class SkipClassConsts
{
    const KEY = 'value';

    public function someMethod()
    {
        return self::KEY;
    }
}

?>
-----
<?php

namespace Rector\Tests\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Fixture;

final class SkipClassConsts
{
    const KEY = 'value';

    public function someMethod()
    {
        return self::KEY;
    }
}

?>
