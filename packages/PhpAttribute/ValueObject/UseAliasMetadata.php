<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\ValueObject;

use PhpParser\Node\Stmt\UseUse;
final class UseAliasMetadata
{
    /**
     * @readonly
     * @var string
     */
    private $shortAttributeName;
    /**
     * @readonly
     * @var string
     */
    private $useImportName;
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\UseUse
     */
    private $useUse;
    public function __construct(string $shortAttributeName, string $useImportName, \PhpParser\Node\Stmt\UseUse $useUse)
    {
        $this->shortAttributeName = $shortAttributeName;
        $this->useImportName = $useImportName;
        $this->useUse = $useUse;
    }
    public function getShortAttributeName() : string
    {
        return $this->shortAttributeName;
    }
    public function getUseImportName() : string
    {
        return $this->useImportName;
    }
    public function getUseUse() : \PhpParser\Node\Stmt\UseUse
    {
        return $this->useUse;
    }
}
