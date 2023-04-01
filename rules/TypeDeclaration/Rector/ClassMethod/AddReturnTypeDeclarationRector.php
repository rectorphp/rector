<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202304\Webmozart\Assert\Assert;
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
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    public function __construct(PhpVersionProvider $phpVersionProvider, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
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
            if (!$this->isName($node, $methodReturnType->getMethod())) {
                continue;
            }
            $objectType = $methodReturnType->getObjectType();
            if (!$this->isObjectType($node, $objectType)) {
                continue;
            }
            $this->processClassMethodNodeWithTypehints($node, $methodReturnType->getReturnType(), $objectType);
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
    private function processClassMethodNodeWithTypehints(ClassMethod $classMethod, Type $newType, ObjectType $objectType) : void
    {
        if ($newType instanceof MixedType) {
            $parentNode = $classMethod->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof Class_) {
                return;
            }
            $className = (string) $this->nodeNameResolver->getName($parentNode);
            $currentObjectType = new ObjectType($className);
            if (!$objectType->equals($currentObjectType) && $classMethod->returnType !== null) {
                return;
            }
        }
        // remove it
        if ($newType instanceof MixedType && !$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::MIXED_TYPE)) {
            $classMethod->returnType = null;
            return;
        }
        // already set and sub type or equal → no change
        if ($this->parentClassMethodTypeOverrideGuard->shouldSkipReturnTypeChange($classMethod, $newType)) {
            return;
        }
        $classMethod->returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($newType, TypeKind::RETURN);
        $this->hasChanged = \true;
    }
}
