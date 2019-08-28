<?php declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\Doctrine\ValueObject\DoctrineClass;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\ColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\CustomIdGeneratorTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\GeneratedValueTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\IdTagValueNode;

final class PhpDocTagNodeFactory
{
    /**
     * @var string
     */
    private $doctrineUuidGeneratorClass;

    public function __construct(string $doctrineUuidGeneratorClass)
    {
        $this->doctrineUuidGeneratorClass = $doctrineUuidGeneratorClass;
    }

    public function createVarTagUuidInterface(): PhpDocTagNode
    {
        $varTagValueNode = new VarTagValueNode(new IdentifierTypeNode(
            '\\' . DoctrineClass::RAMSEY_UUID_INTERFACE
        ), '', '');

        return new PhpDocTagNode('@var', $varTagValueNode);
    }

    public function createIdTag(): PhpDocTagNode
    {
        return new PhpDocTagNode(IdTagValueNode::SHORT_NAME, new IdTagValueNode());
    }

    public function createUuidColumnTag(bool $isNullable): PhpDocTagNode
    {
        $columnTagValueNode = new ColumnTagValueNode(
            null,
            'uuid_binary',
            null,
            null,
            null,
            true,
            $isNullable ? true : null
        );

        return new PhpDocTagNode($columnTagValueNode::SHORT_NAME, $columnTagValueNode);
    }

    public function createGeneratedValueTag(): PhpDocTagNode
    {
        return new PhpDocTagNode(GeneratedValueTagValueNode::SHORT_NAME, new GeneratedValueTagValueNode('CUSTOM'));
    }

    public function createCustomIdGeneratorTag(): PhpDocTagNode
    {
        return new PhpDocTagNode(
            CustomIdGeneratorTagValueNode::SHORT_NAME,
            new CustomIdGeneratorTagValueNode($this->doctrineUuidGeneratorClass)
        );
    }
}
