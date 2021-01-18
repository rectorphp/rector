<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp74\Contract\Rector\DowngradeTypedPropertyRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractDowngradeTypedPropertyRector extends AbstractRector implements DowngradeTypedPropertyRectorInterface
{
    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function autowireAbstractDowngradeTypedPropertyRector(PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }

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

        $this->decoratePropertyWithDocBlock($node, $node->type);

        $node->type = null;

        return $node;
    }

    private function decoratePropertyWithDocBlock(Property $property, Node $typeNode): void
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($property);
        }

        if ($phpDocInfo->getVarTagValueNode() !== null) {
            return;
        }

        $newType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($typeNode);

        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $newType);
    }
}
