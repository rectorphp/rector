<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\StringType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\ValueObject\AddPropertyTypeDeclaration;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220531\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\AddPropertyTypeDeclarationRectorTest
 */
final class AddPropertyTypeDeclarationRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var AddPropertyTypeDeclaration[]
     */
    private $addPropertyTypeDeclarations = [];
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        $configuration = [new \Rector\TypeDeclaration\ValueObject\AddPropertyTypeDeclaration('ParentClass', 'name', new \PHPStan\Type\StringType())];
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add type to property by added rules, mostly public/property by parent type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass extends ParentClass
{
    public $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass extends ParentClass
{
    public string $name;
}
CODE_SAMPLE
, $configuration)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        // type is already known
        if ($node->type !== null) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        foreach ($this->addPropertyTypeDeclarations as $addPropertyTypeDeclaration) {
            if (!$this->isClassReflectionType($classReflection, $addPropertyTypeDeclaration->getClass())) {
                continue;
            }
            if (!$this->isName($node, $addPropertyTypeDeclaration->getPropertyName())) {
                continue;
            }
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($addPropertyTypeDeclaration->getType(), \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY);
            if ($typeNode === null) {
                // invalid configuration
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $node->type = $typeNode;
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\TypeDeclaration\ValueObject\AddPropertyTypeDeclaration::class);
        $this->addPropertyTypeDeclarations = $configuration;
    }
    private function isClassReflectionType(\PHPStan\Reflection\ClassReflection $classReflection, string $type) : bool
    {
        if ($classReflection->hasTraitUse($type)) {
            return \true;
        }
        return $classReflection->isSubclassOf($type);
    }
}
