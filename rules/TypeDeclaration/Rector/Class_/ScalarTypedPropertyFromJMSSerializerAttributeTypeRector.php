<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\Php74\Guard\MakePropertyTypedGuard;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\TypeDeclaration\NodeAnalyzer\JMSTypeAnalyzer;
use Rector\TypeDeclaration\NodeFactory\JMSTypePropertyTypeFactory;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\ScalarTypedPropertyFromJMSSerializerAttributeTypeRector\ScalarTypedPropertyFromJMSSerializerAttributeTypeRectorTest
 */
final class ScalarTypedPropertyFromJMSSerializerAttributeTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private MakePropertyTypedGuard $makePropertyTypedGuard;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private JMSTypeAnalyzer $jmsTypeAnalyzer;
    /**
     * @readonly
     */
    private JMSTypePropertyTypeFactory $jmsTypePropertyTypeFactory;
    public function __construct(MakePropertyTypedGuard $makePropertyTypedGuard, ReflectionResolver $reflectionResolver, JMSTypeAnalyzer $jmsTypeAnalyzer, JMSTypePropertyTypeFactory $jmsTypePropertyTypeFactory)
    {
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
        $this->reflectionResolver = $reflectionResolver;
        $this->jmsTypeAnalyzer = $jmsTypeAnalyzer;
        $this->jmsTypePropertyTypeFactory = $jmsTypePropertyTypeFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add scalar typed property from JMS Serializer Type attribute', [new CodeSample(<<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\Type;

final class SomeClass
{
    #[Type('string')]
    private $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\Type;

final class SomeClass
{
    #[Type('string')]
    private ?string $name = null;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->jmsTypeAnalyzer->hasAtLeastOneUntypedPropertyUsingJmsAttribute($node)) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if ($this->shouldSkipProperty($property, $classReflection)) {
                continue;
            }
            $typeValue = $this->jmsTypeAnalyzer->resolveTypeAttributeValue($property);
            if (!is_string($typeValue)) {
                continue;
            }
            $propertyTypeNode = $this->jmsTypePropertyTypeFactory->createScalarTypeNode($typeValue, $property);
            if (!$propertyTypeNode instanceof Identifier) {
                continue;
            }
            $property->type = new NullableType($propertyTypeNode);
            $property->props[0]->default = $this->nodeFactory->createNull();
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipProperty(Property $property, ClassReflection $classReflection): bool
    {
        if ($property->type instanceof Node || $property->props[0]->default instanceof Expr) {
            return \true;
        }
        if (!$this->jmsTypeAnalyzer->hasPropertyJMSTypeAttribute($property)) {
            return \true;
        }
        return !$this->makePropertyTypedGuard->isLegal($property, $classReflection);
    }
}
