<?php

final class SomeContainer implements ContainerInterface
{
    use ServiceLocatorTrait;
}

trait ServiceLocatorTrait
{
    public function has(string $name)
    {
    }
}

interface ContainerInterface
{
    public function has(string $name);
}

?>
-----
<?php

final class SomeContainer implements ContainerInterface
{
    use ServiceLocatorTrait;
}

trait ServiceLocatorTrait
{
    public function has(string $name)
    {
    }
}

interface ContainerInterface
{
    public function has(string $name);
}

?>
