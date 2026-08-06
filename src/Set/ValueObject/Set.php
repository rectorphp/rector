<?php

declare (strict_types=1);
namespace Rector\Set\ValueObject;

use Rector\Set\Contract\SetInterface;
use RectorPrefix202608\Webmozart\Assert\Assert;
/**
 * @api used by extensions
 *
 * @deprecated Bond the rules themselves instead, by implementing the ComposerPackageConstraintInterface. A set
 * described as an object only existed to be matched against the installed packages; a bonded rule states the exact
 * package version its target API is available from and applies from there upwards, so a plain set file is enough.
 *
 * @see \Rector\VersionBonding\Contract\ComposerPackageConstraintInterface
 * @see https://github.com/rectorphp/rector-src/pull/8296
 */
final class Set implements SetInterface
{
    /**
     * @readonly
     */
    private string $groupName;
    /**
     * @readonly
     */
    private string $setName;
    /**
     * @readonly
     */
    private string $setFilePath;
    public function __construct(string $groupName, string $setName, string $setFilePath)
    {
        $this->groupName = $groupName;
        $this->setName = $setName;
        $this->setFilePath = $setFilePath;
        Assert::fileExists($setFilePath);
    }
    public function getGroupName(): string
    {
        return $this->groupName;
    }
    public function getName(): string
    {
        return $this->setName;
    }
    public function getSetFilePath(): string
    {
        return $this->setFilePath;
    }
}
