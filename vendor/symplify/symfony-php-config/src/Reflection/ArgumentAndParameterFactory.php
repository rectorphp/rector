<?php

declare (strict_types=1);
namespace Symplify\SymfonyPhpConfig\Reflection;

use RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
final class ArgumentAndParameterFactory
{
    /**
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct()
    {
        $this->privatesAccessor = new \RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor();
    }
    /**
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $properties
     * @return object
     */
    public function create(string $className, array $arguments, array $properties)
    {
        $object = new $className(...$arguments);
        foreach ($properties as $name => $value) {
            $this->privatesAccessor->setPrivateProperty($object, $name, $value);
        }
        return $object;
    }
}
