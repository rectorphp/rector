<?php

namespace Rector\Core\Tests\Issues\DowngradeCombo\Fixture;

final class StyleWithCallable implements InterfaceWithCallable
{
    public function ask($validator = null)
    {
    }
}

interface InterfaceWithCallable
{
    public function ask(callable $validator = null);
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\DowngradeCombo\Fixture;

final class StyleWithCallable implements InterfaceWithCallable
{
    public function ask($validator = null)
    {
    }
}

interface InterfaceWithCallable
{
    /**
     * @param callable|null $validator
     */
    public function ask($validator = null);
}

?>
