<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DowngradePhp71\Rector\FunctionLike\AbstractDowngradeRector;
use Rector\DowngradePhp74\Contract\Rector\DowngradeTypedPropertyRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractDowngradeTypedPropertyRector extends AbstractDowngradeRector implements DowngradeTypedPropertyRectorInterface
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->type === null) {
            return null;
        }

        if (! $this->shouldRemoveProperty($node)) {
            return null;
        }

        if ($this->addDocBlock) {
            /** @var PhpDocInfo|null $phpDocInfo */
            $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($phpDocInfo === null) {
                $phpDocInfo = $this->phpDocInfoFactory->createEmpty($node);
            }

            if ($phpDocInfo->getVarTagValueNode() === null) {
                $newType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->type);
                $phpDocInfo->changeVarType($newType);
            }
        }
        $node->type = null;

        return $node;
    }
}
