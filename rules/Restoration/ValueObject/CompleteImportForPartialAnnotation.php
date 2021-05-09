<?php

declare (strict_types=1);
namespace Rector\Restoration\ValueObject;

final class CompleteImportForPartialAnnotation
{
    /**
     * @var string
     */
    private $use;
    /**
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
