<?php

final class AnotherContainer implements AnotherContainerInterface
{
    use AnotherServiceLocatorTrait;
}

trait AnotherServiceLocatorTrait
{
    public function get(string $name)
    {
    }
}

interface AnotherContainerInterface
{
    public function get($name);
}

?>
-----
<?php

final class AnotherContainer implements AnotherContainerInterface
{
    use AnotherServiceLocatorTrait;
}

trait AnotherServiceLocatorTrait
{
    /**
     * @param string $name
     */
    public function get($name)
    {
    }
}

interface AnotherContainerInterface
{
    public function get($name);
}

?>
