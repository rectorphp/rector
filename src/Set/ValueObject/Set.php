<?php

declare (strict_types=1);
namespace Rector\Set\ValueObject;

use Rector\Set\Contract\SetInterface;
use RectorPrefix202410\Webmozart\Assert\Assert;
/**
 * @api used by extensions
 */
final class Set implements SetInterface
{
    /**
     * @readonly
     * @var string
     */
    private $groupName;
    /**
     * @readonly
     * @var string
     */
    private $setName;
    /**
     * @readonly
     * @var string
     */
    private $setFilePath;
    public function __construct(string $groupName, string $setName, string $setFilePath)
    {
        $this->groupName = $groupName;
        $this->setName = $setName;
        $this->setFilePath = $setFilePath;
        Assert::fileExists($setFilePath);
    }
    public function getGroupName() : string
    {
        return $this->groupName;
    }
    public function getName() : string
    {
        return $this->setName;
    }
    public function getSetFilePath() : string
    {
        return $this->setFilePath;
    }
}
