<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp74\Contract\Rector\DowngradeTypedPropertyRectorInterface;

abstract class AbstractDowngradeTypedPropertyRector extends AbstractRector implements DowngradeTypedPropertyRectorInterface
{
    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    /**
     * @required
     */
    public function autowireAbstractDowngradeTypedPropertyRector(PhpDocTypeChanger $phpDocTypeChanger): void
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
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($phpDocInfo->getVarTagValueNode() !== null) {
            return;
        }

        $newType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($typeNode);

        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $newType);
    }
}
