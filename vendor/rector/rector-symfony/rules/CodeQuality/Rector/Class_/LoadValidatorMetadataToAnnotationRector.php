<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
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
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Class_\LoadValidatorMetadataToAnnotationRector\LoadValidatorMetadataToAnnotationRectorTest
 */
final class LoadValidatorMetadataToAnnotationRector extends AbstractRector
{
    /**
     * @readonly
     */
    private MethodCallAnnotationAssertResolver $methodCallAnnotationAssertResolver;
    /**
     * @readonly
     */
    private PropertyAnnotationAssertResolver $propertyAnnotationAssertResolver;
    /**
     * @readonly
     */
    private ClassAnnotationAssertResolver $classAnnotationAssertResolver;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(MethodCallAnnotationAssertResolver $methodCallAnnotationAssertResolver, PropertyAnnotationAssertResolver $propertyAnnotationAssertResolver, ClassAnnotationAssertResolver $classAnnotationAssertResolver, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->methodCallAnnotationAssertResolver = $methodCallAnnotationAssertResolver;
        $this->propertyAnnotationAssertResolver = $propertyAnnotationAssertResolver;
        $this->classAnnotationAssertResolver = $classAnnotationAssertResolver;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
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
        if ($loadValidatorMetadataClassMethod->stmts === null) {
            return null;
        }
        foreach ($loadValidatorMetadataClassMethod->stmts as $key => $methodStmt) {
            // 1. class
            $doctrineAnnotationTagValueNode = $this->classAnnotationAssertResolver->resolve($methodStmt);
            if ($doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                $this->refactorClassAnnotation($node, $doctrineAnnotationTagValueNode, $loadValidatorMetadataClassMethod, $key);
            }
            // 2. class methods
            $classMethodAndAnnotation = $this->methodCallAnnotationAssertResolver->resolve($methodStmt);
            if ($classMethodAndAnnotation instanceof ClassMethodAndAnnotation) {
                $this->refactorClassMethodAndAnnotation($node, $classMethodAndAnnotation, $loadValidatorMetadataClassMethod, $key);
            }
            // 3. properties
            $propertyAndAnnotation = $this->propertyAnnotationAssertResolver->resolve($methodStmt);
            if ($propertyAndAnnotation instanceof PropertyAndAnnotation) {
                $this->refactorPropertyAndAnnotation($node, $propertyAndAnnotation, $loadValidatorMetadataClassMethod, $key);
            }
        }
        // remove empty class method
        if ($loadValidatorMetadataClassMethod->stmts === []) {
            $classMethodStmtKey = $loadValidatorMetadataClassMethod->getAttribute(AttributeKey::STMT_KEY);
            unset($node->stmts[$classMethodStmtKey]);
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
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
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
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
        unset($loadValidatorMetadataClassMethod->stmts[$stmtKey]);
    }
    private function refactorClassAnnotation(Class_ $class, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, ClassMethod $loadValidatorMetadataClassMethod, int $stmtKey) : void
    {
        $classPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        $classPhpDocInfo->addTagValueNode($doctrineAnnotationTagValueNode);
        unset($loadValidatorMetadataClassMethod->stmts[$stmtKey]);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($class);
    }
}
