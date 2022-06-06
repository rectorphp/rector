<?php

declare (strict_types=1);
namespace Rector\Restoration\ValueObject;

final class CompleteImportForPartialAnnotation
{
    /**
     * @readonly
     * @var string
     */
    private $use;
    /**
     * @readonly
     * @var string
     */
    private $alias;
    public function __construct(string $use, string $alias)
    {
        $this->use = $use;
        $this->alias = $alias;
    }
    public function getUse() : string
    {
        return $this->use;
    }
    public function getAlias() : string
    {
        return $this->alias;
    }
}
