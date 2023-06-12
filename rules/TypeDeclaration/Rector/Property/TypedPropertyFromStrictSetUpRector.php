<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\TrustedClassMethodPropertyTypeInferer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector\TypedPropertyFromStrictSetUpRectorTest
 */
final class TypedPropertyFromStrictSetUpRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\TrustedClassMethodPropertyTypeInferer
     */
    private $trustedClassMethodPropertyTypeInferer;
    public function __construct(TrustedClassMethodPropertyTypeInferer $trustedClassMethodPropertyTypeInferer)
    {
        $this->trustedClassMethodPropertyTypeInferer = $trustedClassMethodPropertyTypeInferer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add strict typed property based on setUp() strict typed assigns in TestCase', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    private $value;

    public function setUp()
    {
        $this->value = 1000;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    private int $value;

    public function setUp()
    {
        $this->value = 1000;
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
        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            // type is already set
            if ($property->type !== null) {
                continue;
            }
            // is not private? we cannot be sure about other usage
            if (!$property->isPrivate()) {
                continue;
            }
            $propertyType = $this->trustedClassMethodPropertyTypeInferer->inferProperty($node, $property, $setUpClassMethod);
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
            if (!$propertyTypeNode instanceof Node) {
                continue;
            }
            $property->type = $propertyTypeNode;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
