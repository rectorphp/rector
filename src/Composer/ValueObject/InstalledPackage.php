<?php

declare (strict_types=1);
namespace Rector\Composer\ValueObject;

final class InstalledPackage
{
    /**
     * @readonly
     * @var string
     */
    private $name;
    /**
     * @readonly
     * @var string
     */
    private $version;
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
