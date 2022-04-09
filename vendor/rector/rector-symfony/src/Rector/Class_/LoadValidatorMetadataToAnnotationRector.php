<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\Annotations\ConstraintAnnotationResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/doc/current/components/validator/metadata.html
 * @changelog https://symfony.com/doc/current/validation.html#the-basics-of-validation
 *
 * @see \Rector\Symfony\Tests\Rector\Class_\LoadValidatorMetadataToAnnotationRector\LoadValidatorMetadataToAnnotationRectorTest
 */
final class LoadValidatorMetadataToAnnotationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\Annotations\ConstraintAnnotationResolver
     */
    private $constraintAnnotationResolver;
    public function __construct(\Rector\Symfony\NodeAnalyzer\Annotations\ConstraintAnnotationResolver $constraintAnnotationResolver)
    {
        $this->constraintAnnotationResolver = $constraintAnnotationResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move metadata from loadValidatorMetadata() to property/getter/method annotations', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class SomeClass
{
    private $city;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('city', new Assert\NotBlank([
            'message' => 'City can\'t be blank.',
        ]));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class SomeClass
{
    /**
     * @Assert\NotBlank(message="City can't be blank.")
     */
    private $city;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $loadValidatorMetadataClassMethod = $node->getMethod('loadValidatorMetadata');
        if (!$loadValidatorMetadataClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        // @todo extract annotations from loadValidatorMetadata()
        $annotationsToMethodNames = $this->constraintAnnotationResolver->resolveGetterTagValueNodes($loadValidatorMetadataClassMethod);
        foreach ($annotationsToMethodNames as $methodName => $doctrineTagValueNode) {
            $classMethod = $node->getMethod($methodName);
            if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            $getterPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $getterPhpDocInfo->addTagValueNode($doctrineTagValueNode);
        }
        $annotationsToPropertyNames = $this->constraintAnnotationResolver->resolvePropertyTagValueNodes($loadValidatorMetadataClassMethod);
        foreach ($annotationsToPropertyNames as $propertyName => $doctrineTagValueNode) {
            $property = $node->getProperty($propertyName);
            if (!$property instanceof \PhpParser\Node\Stmt\Property) {
                continue;
            }
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            $propertyPhpDocInfo->addTagValueNode($doctrineTagValueNode);
        }
        // in the end this should be only empty class method removal
        $this->removeNode($loadValidatorMetadataClassMethod);
        return $node;
    }
}
