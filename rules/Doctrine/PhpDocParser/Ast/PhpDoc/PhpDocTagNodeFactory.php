<?php

declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Doctrine\ColumnTagValueNodeFactory;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Doctrine\JoinColumnTagValueNodeFactory;

final class PhpDocTagNodeFactory
{
    /**
     * @var ColumnTagValueNodeFactory
     */
    private $columnTagValueNodeFactory;

    /**
     * @var ArrayPartPhpDocTagPrinter
     */
    private $arrayPartPhpDocTagPrinter;

    /**
     * @var TagValueNodePrinter
     */
    private $tagValueNodePrinter;

    /**
     * @var JoinColumnTagValueNodeFactory
     */
    private $joinColumnTagValueNodeFactory;

    public function __construct(
        ColumnTagValueNodeFactory $columnTagValueNodeFactory,
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter,
        JoinColumnTagValueNodeFactory $joinColumnTagValueNodeFactory
    ) {
        $this->columnTagValueNodeFactory = $columnTagValueNodeFactory;
        $this->arrayPartPhpDocTagPrinter = $arrayPartPhpDocTagPrinter;
        $this->tagValueNodePrinter = $tagValueNodePrinter;
        $this->joinColumnTagValueNodeFactory = $joinColumnTagValueNodeFactory;
    }

    public function createVarTagIntValueNode(): AttributeAwareVarTagValueNode
    {
        return $this->createVarTagValueNodeWithType(new IdentifierTypeNode('int'));
    }

    public function createIdColumnTagValueNode(): ColumnTagValueNode
    {
        return $this->columnTagValueNodeFactory->createFromItems([
            'type' => 'integer',
        ]);
    }

    private function createVarTagValueNodeWithType(TypeNode $typeNode): AttributeAwareVarTagValueNode
    {
        return new AttributeAwareVarTagValueNode($typeNode, '', '');
    }
}
