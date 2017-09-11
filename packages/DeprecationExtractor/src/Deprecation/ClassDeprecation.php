<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Deprecation;

use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;

final class ClassDeprecation implements DeprecationInterface
{
    /**
     * @var string
     */
    private $oldClass;
    /**
     * @var string
     */
    private $newClass;

    public function __construct(string $oldClass, string $newClass)
    {
        $this->oldClass = $oldClass;
        $this->newClass = $newClass;
    }

    public function getOldClass(): string
    {
        return $this->oldClass;
    }

    /**
     * @return string
     */
    public function getNewClass(): string
    {
        return $this->newClass;
    }
}
