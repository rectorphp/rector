<?php

final class SomeContainer implements ContainerInterface
{
    use ServiceLocatorTrait;
}

trait ServiceLocatorTrait
{
    public function get(string $name)
    {
    }
}

interface ContainerInterface
{
    public function get($name);
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
    /**
     * @param string $name
     */
    public function get($name)
    {
    }
}

interface ContainerInterface
{
    public function get($name);
}

?>
