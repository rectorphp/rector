<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

final class MethodCallRename
{
    /**
     * @var string
     */
    private $oldClass;

    /**
     * @var string
     */
    private $oldMethod;

    /**
     * @var string
     */
    private $newMethod;

    public function __construct(string $oldClass, string $oldMethod, string $newMethod)
    {
        $this->oldClass = $oldClass;
        $this->oldMethod = $oldMethod;
        $this->newMethod = $newMethod;
    }

    public function getOldClass(): string
    {
        return $this->oldClass;
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getNewMethod(): string
    {
        return $this->newMethod;
    }
}
