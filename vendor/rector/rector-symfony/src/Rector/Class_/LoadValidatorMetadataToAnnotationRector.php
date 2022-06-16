<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\Annotations\ClassAnnotationAssertResolver;
use Rector\Symfony\NodeAnalyzer\Annotations\MethodCallAnnotationAssertResolver;
use Rector\Symfony\NodeAnalyzer\Annotations\PropertyAnnotationAssertResolver;
use Rector\Symfony\ValueObject\ValidatorAssert\ClassMethodAndAnnotation;
use Rector\Symfony\ValueObject\ValidatorAssert\PropertyAndAnnotation;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/doc/current/components/validator/metadata.html
 * @changelog https://symfony.com/doc/current/validation.html#the-basics-of-validation
 *
 * @see \Rector\Symfony\Tests\Rector\Class_\LoadValidatorMetadataToAnnotationRector\LoadValidatorMetadataToAnnotationRectorTest
 */
final class LoadValidatorMetadataToAnnotationRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\Annotations\MethodCallAnnotationAssertResolver
     */
    private $methodCallAnnotationAssertResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\Annotations\PropertyAnnotationAssertResolver
     */
    private $propertyAnnotationAssertResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\Annotations\ClassAnnotationAssertResolver
     */
    private $classAnnotationAssertResolver;
    public function __construct(MethodCallAnnotationAssertResolver $methodCallAnnotationAssertResolver, PropertyAnnotationAssertResolver $propertyAnnotationAssertResolver, ClassAnnotationAssertResolver $classAnnotationAssertResolver)
    {
        $this->methodCallAnnotationAssertResolver = $methodCallAnnotationAssertResolver;
        $this->propertyAnnotationAssertResolver = $propertyAnnotationAssertResolver;
        $this->classAnnotationAssertResolver = $classAnnotationAssertResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move metadata from loadValidatorMetadata() to property/getter/method annotations', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $loadValidatorMetadataClassMethod = $node->getMethod('loadValidatorMetadata');
        if (!$loadValidatorMetadataClassMethod instanceof ClassMethod) {
            return null;
        }
        foreach ((array) $loadValidatorMetadataClassMethod->stmts as $stmtKey => $classStmt) {
            // 1. class
            $doctrineAnnotationTagValueNode = $this->classAnnotationAssertResolver->resolve($classStmt);
            if ($doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                $this->refactorClassAnnotation($node, $doctrineAnnotationTagValueNode, $loadValidatorMetadataClassMethod, $stmtKey);
            }
            // 2. class methods
            $classMethodAndAnnotation = $this->methodCallAnnotationAssertResolver->resolve($classStmt);
            if ($classMethodAndAnnotation instanceof ClassMethodAndAnnotation) {
                $this->refactorClassMethodAndAnnotation($node, $classMethodAndAnnotation, $loadValidatorMetadataClassMethod, $stmtKey);
            }
            // 3. properties
            $propertyAndAnnotation = $this->propertyAnnotationAssertResolver->resolve($classStmt);
            if ($propertyAndAnnotation instanceof PropertyAndAnnotation) {
                $this->refactorPropertyAndAnnotation($node, $propertyAndAnnotation, $loadValidatorMetadataClassMethod, $stmtKey);
            }
        }
        // remove empty class method
        if ((array) $loadValidatorMetadataClassMethod->stmts === []) {
            $this->removeNode($loadValidatorMetadataClassMethod);
        }
        return $node;
    }
    private function refactorClassMethodAndAnnotation(Class_ $class, ClassMethodAndAnnotation $classMethodAndAnnotation, ClassMethod $loadValidatorMetadataClassMethod, int $stmtKey) : void
    {
        foreach ($classMethodAndAnnotation->getPossibleMethodNames() as $possibleMethodName) {
            $classMethod = $class->getMethod($possibleMethodName);
            if (!$classMethod instanceof ClassMethod) {
                continue;
            }
            $getterPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $getterPhpDocInfo->addTagValueNode($classMethodAndAnnotation->getDoctrineAnnotationTagValueNode());
            unset($loadValidatorMetadataClassMethod->stmts[$stmtKey]);
        }
    }
    private function refactorPropertyAndAnnotation(Class_ $class, PropertyAndAnnotation $propertyAndAnnotation, ClassMethod $loadValidatorMetadataClassMethod, int $stmtKey) : void
    {
        $property = $class->getProperty($propertyAndAnnotation->getProperty());
        if (!$property instanceof Property) {
            return;
        }
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $propertyPhpDocInfo->addTagValueNode($propertyAndAnnotation->getDoctrineAnnotationTagValueNode());
        unset($loadValidatorMetadataClassMethod->stmts[$stmtKey]);
    }
    private function refactorClassAnnotation(Class_ $class, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, ClassMethod $loadValidatorMetadataClassMethod, int $stmtKey) : void
    {
        $classPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        $classPhpDocInfo->addTagValueNode($doctrineAnnotationTagValueNode);
        unset($loadValidatorMetadataClassMethod->stmts[$stmtKey]);
    }
}
