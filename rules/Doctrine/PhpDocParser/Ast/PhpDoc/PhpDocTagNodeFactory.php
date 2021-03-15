<?php

declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Doctrine\ColumnTagValueNodeFactory;

final class PhpDocTagNodeFactory
{
    /**
     * @var ColumnTagValueNodeFactory
     */
    private $columnTagValueNodeFactory;

    public function __construct(ColumnTagValueNodeFactory $columnTagValueNodeFactory)
    {
        $this->columnTagValueNodeFactory = $columnTagValueNodeFactory;
    }

    public function createVarTagIntValueNode(): AttributeAwareVarTagValueNode
    {
        $identifierTypeNode = new IdentifierTypeNode('int');
        return new AttributeAwareVarTagValueNode($identifierTypeNode, '', '');
    }

    public function createIdColumnTagValueNode(): ColumnTagValueNode
    {
        return $this->columnTagValueNodeFactory->createFromItems([
            'type' => 'integer',
        ]);
    }
}
