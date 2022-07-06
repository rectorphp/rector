<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
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
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        // type is already set
        if ($node->type !== null) {
            return null;
        }
        // is not private? we cannot be sure about other usage
        if (!$node->isPrivate()) {
            return null;
        }
        $propertyType = $this->trustedClassMethodPropertyTypeInferer->inferProperty($node, MethodName::SET_UP);
        if (!$propertyType instanceof Type) {
            return null;
        }
        // skip unless a class type; we cannot be sure about traits
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
        if (!$propertyTypeNode instanceof Node) {
            return null;
        }
        $node->type = $propertyTypeNode;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
