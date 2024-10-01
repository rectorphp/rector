<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Doctrine\NodeManipulator\ToOneRelationPropertyTypeResolver;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator;
use Rector\ValueObject\PhpVersion;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202410\Webmozart\Assert\Assert;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\TypedPropertyFromToOneRelationTypeRectorTest
 */
final class TypedPropertyFromToOneRelationTypeRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator
     */
    private $propertyTypeDecorator;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeManipulator\ToOneRelationPropertyTypeResolver
     */
    private $toOneRelationPropertyTypeResolver;
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public const FORCE_NULLABLE = 'force_nullable';
    /**
     * @var bool
     */
    private $forceNullable = \true;
    public function __construct(PropertyTypeDecorator $propertyTypeDecorator, PhpDocTypeChanger $phpDocTypeChanger, ToOneRelationPropertyTypeResolver $toOneRelationPropertyTypeResolver, PhpVersionProvider $phpVersionProvider, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper)
    {
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->toOneRelationPropertyTypeResolver = $toOneRelationPropertyTypeResolver;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Complete @var annotations or types based on @ORM\\*toOne annotations or attributes', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?\App\Company\Entity\Company $company = null;
}
CODE_SAMPLE
, ['force_nullable' => \true]), new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     * @ORM\JoinColumn(nullable=false)
     */
    private \App\Company\Entity\Company $company;
}
CODE_SAMPLE
, ['force_nullable' => \false])]);
    }
    /**
     * @param array<string, bool> $configuration
     */
    public function configure(array $configuration) : void
    {
        if (isset($configuration[self::FORCE_NULLABLE])) {
            Assert::boolean($configuration[self::FORCE_NULLABLE]);
            $this->forceNullable = $configuration[self::FORCE_NULLABLE];
        }
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Stmt\Property
    {
        if ($node->type !== null) {
            return null;
        }
        $propertyType = $this->toOneRelationPropertyTypeResolver->resolve($node, $this->forceNullable);
        if (!$propertyType instanceof Type) {
            return null;
        }
        if ($propertyType instanceof MixedType) {
            return null;
        }
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
        if (!$typeNode instanceof Node) {
            return null;
        }
        $this->completePropertyTypeOrVarDoc($propertyType, $typeNode, $node);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\ComplexType|\PhpParser\Node\Identifier $typeNode
     */
    private function completePropertyTypeOrVarDoc(Type $propertyType, $typeNode, Property $property) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersion::PHP_74)) {
            if ($propertyType instanceof UnionType) {
                $this->propertyTypeDecorator->decoratePropertyUnionType($propertyType, $typeNode, $property, $phpDocInfo);
                return;
            }
            $property->type = $typeNode;
            return;
        }
        $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $propertyType);
    }
}
