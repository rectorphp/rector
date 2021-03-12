<?php

declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser\Ast\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\AroundSpaces;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinTableTagValueNode;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Doctrine\ColumnTagValueNodeFactory;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Doctrine\JoinColumnTagValueNodeFactory;
use Rector\Doctrine\Uuid\JoinTableNameResolver;

final class PhpDocTagNodeFactory
{
    /**
     * @var string
     */
    private const UUID = 'uuid';

    /**
     * @var JoinTableNameResolver
     */
    private $joinTableNameResolver;

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
        JoinTableNameResolver $joinTableNameResolver,
        ColumnTagValueNodeFactory $columnTagValueNodeFactory,
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter,
        JoinColumnTagValueNodeFactory $joinColumnTagValueNodeFactory
    ) {
        $this->joinTableNameResolver = $joinTableNameResolver;
        $this->columnTagValueNodeFactory = $columnTagValueNodeFactory;
        $this->arrayPartPhpDocTagPrinter = $arrayPartPhpDocTagPrinter;
        $this->tagValueNodePrinter = $tagValueNodePrinter;
        $this->joinColumnTagValueNodeFactory = $joinColumnTagValueNodeFactory;
    }

    public function createVarTagIntValueNode(): AttributeAwareVarTagValueNode
    {
        return $this->createVarTagValueNodeWithType(new IdentifierTypeNode('int'));
    }

    public function createUuidInterfaceVarTagValueNode(): AttributeAwareVarTagValueNode
    {
        $identifierTypeNode = new IdentifierTypeNode('\Ramsey\Uuid\UuidInterface');

        return $this->createVarTagValueNodeWithType($identifierTypeNode);
    }

    public function createIdColumnTagValueNode(): ColumnTagValueNode
    {
        return $this->columnTagValueNodeFactory->createFromItems([
            'type' => 'integer',
        ]);
    }

    public function createUuidColumnTagValueNode(bool $isNullable): ColumnTagValueNode
    {
        return $this->columnTagValueNodeFactory->createFromItems([
            'type' => 'uuid_binary',
            'unique' => true,
            'nullable' => $isNullable ? true : null,
        ]);
    }

    public function createJoinTableTagNode(Property $property): PhpDocTagNode
    {
        $uuidJoinTable = $this->joinTableNameResolver->resolveManyToManyUuidTableNameForProperty($property);

        $joinColumnTagValueNode = $this->joinColumnTagValueNodeFactory->createFromItems([
            'referencedColumnName' => self::UUID,
        ]);

        $uuidJoinColumnTagValueNodes = [$joinColumnTagValueNode];

        $joinTableTagValueNode = new JoinTableTagValueNode(
            $this->arrayPartPhpDocTagPrinter,
            $this->tagValueNodePrinter,
            $uuidJoinTable,
            null,
            $uuidJoinColumnTagValueNodes,
            $uuidJoinColumnTagValueNodes,
            '',
            new AroundSpaces('', ''),
            new AroundSpaces('', '')
        );

        return new SpacelessPhpDocTagNode($joinTableTagValueNode->getShortName(), $joinTableTagValueNode);
    }

    public function createJoinColumnTagNode(bool $isNullable): JoinColumnTagValueNode
    {
        return $this->joinColumnTagValueNodeFactory->createFromItems([
            'referencedColumn' => self::UUID,
            'nullable' => $isNullable,
        ]);
    }

    private function createVarTagValueNodeWithType(TypeNode $typeNode): AttributeAwareVarTagValueNode
    {
        return new AttributeAwareVarTagValueNode($typeNode, '', '');
    }
}
