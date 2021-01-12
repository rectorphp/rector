<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

class Dependency
{
    public function unusedpublicMethod()
    {
    }

    public function run()
    {
    }
}

final class DependencyInjection
{
    public function __construct(Dependency $dependency)
    {
        $dependency->run();
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

class Dependency
{
    public function run()
    {
    }
}

final class DependencyInjection
{
    public function __construct(Dependency $dependency)
    {
        $dependency->run();
    }
}

?>
