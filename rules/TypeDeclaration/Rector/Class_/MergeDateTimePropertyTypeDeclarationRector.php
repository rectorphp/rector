<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\MergeDateTimePropertyTypeDeclarationRector\MergeDateTimePropertyTypeDeclarationRectorTest
 */
final class MergeDateTimePropertyTypeDeclarationRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Set DateTime to DateTimeInterface for DateTime property with DateTimeInterface docblock', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var DateTimeInterface
     */
    private DateTime $dateTime;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private DateTimeInterface $dateTime;
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
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if (!$property->type instanceof Node) {
                continue;
            }
            if (!$property->type instanceof FullyQualified) {
                continue;
            }
            if ($property->type->toString() !== 'DateTime') {
                continue;
            }
            if (!$property->isPrivate()) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            if (!$phpDocInfo instanceof PhpDocInfo) {
                continue;
            }
            $varType = $phpDocInfo->getVarType();
            $className = $varType instanceof TypeWithClassName ? $this->nodeTypeResolver->getFullyQualifiedClassName($varType) : null;
            if ($className === 'DateTimeInterface') {
                $phpDocInfo->removeByType(VarTagValueNode::class);
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
                $property->type = new FullyQualified('DateTimeInterface');
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
