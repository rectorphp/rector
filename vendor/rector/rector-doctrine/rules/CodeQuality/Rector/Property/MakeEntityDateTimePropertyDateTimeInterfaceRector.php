<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Doctrine\NodeAnalyzer\DoctrineEntityDetector;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see related to maker bundle https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
 *
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector\MakeEntityDateTimePropertyDateTimeInterfaceRectorTest
 */
final class MakeEntityDateTimePropertyDateTimeInterfaceRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer
     */
    private $docBlockClassRenamer;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\DoctrineEntityDetector
     */
    private $doctrineEntityDetector;
    public function __construct(DocBlockClassRenamer $docBlockClassRenamer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, DoctrineEntityDetector $doctrineEntityDetector)
    {
        $this->docBlockClassRenamer = $docBlockClassRenamer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->doctrineEntityDetector = $doctrineEntityDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make maker bundle generate DateTime property accept DateTimeInterface too', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTime|null
     */
    private $bornAt;

    public function setBornAt(DateTimeInterface $bornAt)
    {
        $this->bornAt = $bornAt;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTimeInterface|null
     */
    private $bornAt;

    public function setBornAt(DateTimeInterface $bornAt)
    {
        $this->bornAt = $bornAt;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->doctrineEntityDetector->detect($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            if (!$phpDocInfo instanceof PhpDocInfo) {
                continue;
            }
            $varType = $phpDocInfo->getVarType();
            if ($varType instanceof UnionType) {
                $varType = TypeCombinator::removeNull($varType);
            }
            if (!$varType->equals(new ObjectType('DateTime'))) {
                continue;
            }
            $this->changePropertyType($property, 'DateTime', 'DateTimeInterface');
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function changePropertyType(Property $property, string $oldClass, string $newClass) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $oldToNewTypes = [new OldToNewType(new FullyQualifiedObjectType($oldClass), new FullyQualifiedObjectType($newClass))];
        $hasChanged = $this->docBlockClassRenamer->renamePhpDocType($phpDocInfo, $oldToNewTypes, $property);
        if (!$hasChanged) {
            return;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
    }
}
