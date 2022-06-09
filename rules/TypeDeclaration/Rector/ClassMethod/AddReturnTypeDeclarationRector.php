<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220609\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\AddReturnTypeDeclarationRectorTest
 */
final class AddReturnTypeDeclarationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var AddReturnTypeDeclaration[]
     */
    private $methodReturnTypes = [];
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(TypeComparator $typeComparator, PhpVersionProvider $phpVersionProvider)
    {
        $this->typeComparator = $typeComparator;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        $arrayType = new ArrayType(new MixedType(), new MixedType());
        return new RuleDefinition('Changes defined return typehint of method and class.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function getData()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function getData(): array
    {
    }
}
CODE_SAMPLE
, [new AddReturnTypeDeclaration('SomeClass', 'getData', $arrayType)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($this->methodReturnTypes as $methodReturnType) {
            if (!$this->isObjectType($node, $methodReturnType->getObjectType())) {
                continue;
            }
            if (!$this->isName($node, $methodReturnType->getMethod())) {
                continue;
            }
            $this->processClassMethodNodeWithTypehints($node, $methodReturnType->getReturnType());
            if (!$this->hasChanged) {
                return null;
            }
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, AddReturnTypeDeclaration::class);
        $this->methodReturnTypes = $configuration;
    }
    private function processClassMethodNodeWithTypehints(ClassMethod $classMethod, Type $newType) : void
    {
        // remove it
        if ($newType instanceof MixedType && !$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::MIXED_TYPE)) {
            $classMethod->returnType = null;
            return;
        }
        // already set → no change
        if ($classMethod->returnType !== null) {
            $currentReturnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($classMethod->returnType);
            if ($this->typeComparator->areTypesEqual($currentReturnType, $newType)) {
                return;
            }
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($newType, TypeKind::RETURN);
        $classMethod->returnType = $returnTypeNode;
        $this->hasChanged = \true;
    }
}
