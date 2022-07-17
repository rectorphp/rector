<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpDoc;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasImportTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasTagValueNode;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
final class CustomPHPStanDetector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @api
     */
    public function isCustomType(Type $definedType, Node $node) : bool
    {
        if (!$definedType instanceof NonExistingObjectType) {
            return \false;
        }
        // start from current Node to lookup parent
        $parentNode = $node;
        $className = $definedType->getClassName();
        while ($parentNode instanceof Node) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($parentNode);
            if ($this->isUsePHPStanImportType($phpDocInfo, $className)) {
                return \true;
            }
            if ($this->isUsePHPStanType($phpDocInfo, $className)) {
                return \true;
            }
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }
        return \false;
    }
    private function isUsePHPStanImportType(PhpDocInfo $phpDocInfo, string $className) : bool
    {
        $tagsByName = $phpDocInfo->getTagsByName('phpstan-import-type');
        foreach ($tagsByName as $tags) {
            if (!$tags->value instanceof TypeAliasImportTagValueNode) {
                continue;
            }
            if ($tags->value->importedAlias === $className) {
                return \true;
            }
        }
        return \false;
    }
    private function isUsePHPStanType(PhpDocInfo $phpDocInfo, string $className) : bool
    {
        $tagsByName = $phpDocInfo->getTagsByName('phpstan-type');
        foreach ($tagsByName as $tags) {
            if (!$tags->value instanceof TypeAliasTagValueNode) {
                continue;
            }
            if ($tags->value->alias === $className) {
                return \true;
            }
        }
        return \false;
    }
}
