<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\ValueObject;

use PhpParser\Node\UseItem;
final class UseAliasMetadata
{
    /**
     * @readonly
     */
    private string $shortAttributeName;
    /**
     * @readonly
     */
    private string $useImportName;
    /**
     * @readonly
     */
    private UseItem $useItem;
    public function __construct(string $shortAttributeName, string $useImportName, UseItem $useItem)
    {
        $this->shortAttributeName = $shortAttributeName;
        $this->useImportName = $useImportName;
        $this->useItem = $useItem;
    }
    public function getShortAttributeName() : string
    {
        return $this->shortAttributeName;
    }
    public function getUseImportName() : string
    {
        return $this->useImportName;
    }
    public function getUseUse() : UseItem
    {
        return $this->useItem;
    }
}
