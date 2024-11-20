<?php

declare (strict_types=1);
namespace Rector\Composer\ValueObject;

final class InstalledPackage
{
    /**
     * @readonly
     */
    private string $name;
    /**
     * @readonly
     */
    private string $version;
    public function __construct(string $name, string $version)
    {
        $this->name = $name;
        $this->version = $version;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getVersion() : string
    {
        return $this->version;
    }
}
