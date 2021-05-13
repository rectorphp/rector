<?php

declare (strict_types=1);
namespace Rector\Composer\ValueObject;

final class RenamePackage
{
    /**
     * @var string
     */
    private $oldPackageName;
    /**
     * @var string
     */
    private $newPackageName;
    public function __construct(string $oldPackageName, string $newPackageName)
    {
        $this->oldPackageName = $oldPackageName;
        $this->newPackageName = $newPackageName;
    }
    public function getOldPackageName() : string
    {
        return $this->oldPackageName;
    }
    public function getNewPackageName() : string
    {
        return $this->newPackageName;
    }
}
