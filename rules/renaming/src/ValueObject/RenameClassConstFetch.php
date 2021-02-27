<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Renaming\Contract\RenameClassConstFetchInterface;

final class RenameClassConstFetch implements RenameClassConstFetchInterface
{
    /**
     * @var string
     */
    private $oldClass;

    /**
     * @var string
     */
    private $oldConstant;

    /**
     * @var string
     */
    private $newConstant;

    public function __construct(string $oldClass, string $oldConstant, string $newConstant)
    {
        $this->oldClass = $oldClass;
        $this->oldConstant = $oldConstant;
        $this->newConstant = $newConstant;
    }

    public function getOldObjectType(): ObjectType
    {
        return new ObjectType($this->oldClass);
    }

    public function getOldConstant(): string
    {
        return $this->oldConstant;
    }

    public function getNewConstant(): string
    {
        return $this->newConstant;
    }
}
