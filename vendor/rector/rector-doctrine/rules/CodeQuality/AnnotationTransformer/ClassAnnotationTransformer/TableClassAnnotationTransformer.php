<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AnnotationTransformer\ClassAnnotationTransformer;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Doctrine\CodeQuality\Contract\ClassAnnotationTransformerInterface;
use Rector\Doctrine\CodeQuality\DocTagNodeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
final class TableClassAnnotationTransformer implements ClassAnnotationTransformerInterface
{
    /**
     * @var string
     */
    private const TABLE_KEY = 'table';
    public function transform(EntityMapping $entityMapping, PhpDocInfo $classPhpDocInfo) : void
    {
        $classMapping = $entityMapping->getClassMapping();
        $table = $classMapping[self::TABLE_KEY] ?? null;
        if (isset($classMapping['type']) && $classMapping['type'] !== 'entity') {
            return;
        }
        $arrayItemNodes = [];
        if (\is_string($table)) {
            $arrayItemNodes[] = new ArrayItemNode(new StringNode($table), self::TABLE_KEY);
        }
        $uniqueConstraints = $classMapping['uniqueConstraints'] ?? null;
        if ($uniqueConstraints) {
            $uniqueConstraintDoctrineAnnotationTagValueNodes = [];
            foreach ($uniqueConstraints as $name => $uniqueConstraint) {
                $columnArrayItems = [];
                foreach ($uniqueConstraint['columns'] as $column) {
                    $columnArrayItems[] = new ArrayItemNode(new StringNode($column));
                }
                $columnCurlList = new CurlyListNode($columnArrayItems);
                $uniqueConstraintDoctrineAnnotationTagValueNodes[] = new ArrayItemNode(new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('@\\Doctrine\\ORM\\Mapping\\UniqueConstraint'), null, [new ArrayItemNode(new StringNode($name), 'name'), new ArrayItemNode($columnCurlList, 'columns')]));
            }
            $arrayItemNodes[] = new ArrayItemNode(new CurlyListNode($uniqueConstraintDoctrineAnnotationTagValueNodes), 'uniqueConstraints');
        }
        $spacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode($arrayItemNodes, $this->getClassName());
        $classPhpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
    }
    public function getClassName() : string
    {
        return 'Doctrine\\ORM\\Mapping\\Table';
    }
}
