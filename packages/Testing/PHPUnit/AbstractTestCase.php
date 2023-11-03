<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

abstract class AbstractTestCase extends \Rector\Testing\PHPUnit\AbstractLazyTestCase
{
    /**
     * @param string[] $configFiles
     */
    protected function bootFromConfigFiles(array $configFiles) : void
    {
        $rectorConfig = self::getContainer();
        foreach ($configFiles as $configFile) {
            $callable = (require $configFile);
            $callable($rectorConfig);
        }
    }
    /**
     * Syntax-sugar to remove static
     * @deprecated Only for BC
     *
     * @template T of object
     * @param class-string<T> $type
     * @return T
     */
    protected function getService(string $type) : object
    {
        return $this->make($type);
    }
}
