<?php

namespace Rector\Tests\Issues\CallableFixture;

final class StyleWithCallable implements InterfaceWithCallable
{
    public function ask(?string $default = null, $validator = null)
    {
    }
}

interface InterfaceWithCallable
{
    public function ask(?string $default = null, callable $validator = null);
}

?>
-----
<?php

namespace Rector\Tests\Issues\CallableFixture;

final class StyleWithCallable implements InterfaceWithCallable
{
    /**
     * @param string|null $default
     */
    public function ask($default = null, $validator = null)
    {
    }
}

interface InterfaceWithCallable
{
    /**
     * @param callable|null $validator
     * @param string|null $default
     */
    public function ask($default = null, $validator = null);
}

?>
