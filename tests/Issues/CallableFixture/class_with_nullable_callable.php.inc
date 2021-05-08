<?php

namespace Rector\Tests\Issues\CallableFixture;

final class StyleWithCallable implements InterfaceWithCallable
{
    public function ask($id = null)
    {
    }
}

interface InterfaceWithCallable
{
    public function ask(callable $id = null);
}

?>
-----
<?php

namespace Rector\Tests\Issues\CallableFixture;

final class StyleWithCallable implements InterfaceWithCallable
{
    public function ask($id = null)
    {
    }
}

interface InterfaceWithCallable
{
    /**
     * @param callable|null $id
     */
    public function ask($id = null);
}

?>
