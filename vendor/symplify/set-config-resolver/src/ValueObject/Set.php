<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SetConfigResolver\ValueObject;

use Symplify\SmartFileSystem\SmartFileInfo;
final class Set
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var SmartFileInfo
     */
    private $setFileInfo;
    public function __construct(string $name, SmartFileInfo $setFileInfo)
    {
        $this->name = $name;
        $this->setFileInfo = $setFileInfo;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getSetFileInfo() : SmartFileInfo
    {
        return $this->setFileInfo;
    }
    public function getSetPathname() : string
    {
        return $this->setFileInfo->getPathname();
    }
}
