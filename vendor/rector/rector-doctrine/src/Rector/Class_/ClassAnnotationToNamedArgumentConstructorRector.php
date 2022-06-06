<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\Rector\Class_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\Core\NodeManipulator\ClassInsertManipulator;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\Doctrine\NodeAnalyzer\AssignPropertyFetchAnalyzer;
use RectorPrefix20220606\Rector\Doctrine\NodeFactory\ConstructClassMethodFactory;
use RectorPrefix20220606\Rector\Doctrine\NodeFactory\ConstructorClassMethodAssignFactory;
use RectorPrefix20220606\Rector\Doctrine\NodeFactory\ParamFactory;
use RectorPrefix20220606\Rector\Doctrine\NodeManipulator\IssetDimFetchCleaner;
use RectorPrefix20220606\Rector\Doctrine\ValueObject\AssignToPropertyFetch;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/doctrine/annotations/blob/1.13.x/docs/en/custom.rst#optional-constructors-with-named-parameters
 *
 * @see \Rector\Doctrine\Tests\Rector\Class_\ClassAnnotationToNamedArgumentConstructorRector\ClassAnnotationToNamedArgumentConstructorRectorTest
 */
final class ClassAnnotationToNamedArgumentConstructorRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeFactory\ParamFactory
     */
    private $paramFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeFactory\ConstructClassMethodFactory
     */
    private $constructClassMethodFactory;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AssignPropertyFetchAnalyzer
     */
    private $assignPropertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeManipulator\IssetDimFetchCleaner
     */
    private $issetDimFetchCleaner;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeFactory\ConstructorClassMethodAssignFactory
     */
    private $constructorClassMethodAssignFactory;
    public function __construct(ParamFactory $paramFactory, ConstructClassMethodFactory $constructClassMethodFactory, ClassInsertManipulator $classInsertManipulator, AssignPropertyFetchAnalyzer $assignPropertyFetchAnalyzer, IssetDimFetchCleaner $issetDimFetchCleaner, ConstructorClassMethodAssignFactory $constructorClassMethodAssignFactory)
    {
        $this->paramFactory = $paramFactory;
        $this->constructClassMethodFactory = $constructClassMethodFactory;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->assignPropertyFetchAnalyzer = $assignPropertyFetchAnalyzer;
        $this->issetDimFetchCleaner = $issetDimFetchCleaner;
        $this->constructorClassMethodAssignFactory = $constructorClassMethodAssignFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Decorate classic array-based class annotation with named parameters', [new CodeSample(<<<'CODE_SAMPLE'
/**
 * @Annotation
 */
class SomeAnnotation
{
    /**
     * @var string
     */
    private $foo;

    public function __construct(array $values)
    {
        $this->foo = $values['foo'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 */
class SomeAnnotation
{
    /**
     * @var string
     */
    private $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
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
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        if ($this->shouldSkipPhpDocInfo($phpDocInfo)) {
            return null;
        }
        $doctrineAnnotationTagValueNode = new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('Doctrine\\Common\\Annotations\\Annotation\\NamedArgumentConstructor'));
        $phpDocInfo->addTagValueNode($doctrineAnnotationTagValueNode);
        $classMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$classMethod instanceof ClassMethod) {
            return $this->decorateClassWithAssignClassMethod($node);
        }
        if (!$this->hasSingleArrayParam($classMethod)) {
            return null;
        }
        /** @var Variable $paramVariable */
        $paramVariable = $classMethod->params[0]->var;
        $optionalParamNames = $this->issetDimFetchCleaner->resolveOptionalParamNames($classMethod, $paramVariable);
        $this->issetDimFetchCleaner->removeArrayDimFetchIssets($classMethod, $paramVariable);
        $assignsToPropertyFetch = $this->assignPropertyFetchAnalyzer->resolveAssignToPropertyFetch($classMethod);
        $this->replaceAssignsByParam($assignsToPropertyFetch);
        $classMethod->params = $this->paramFactory->createFromAssignsToPropertyFetch($assignsToPropertyFetch, $optionalParamNames);
        // include assigns for optional params - these do not have assign in the root, as they're hidden in if isset/check
        // so we have to add them
        $assigns = $this->constructorClassMethodAssignFactory->createFromParamNames($optionalParamNames);
        if ($assigns !== []) {
            $classMethod->stmts = \array_merge((array) $classMethod->stmts, $assigns);
        }
        return $node;
    }
    private function shouldSkipPhpDocInfo(PhpDocInfo $phpDocInfo) : bool
    {
        if (!$phpDocInfo->hasByNames(['annotation', 'Annotation'])) {
            return \true;
        }
        return $phpDocInfo->hasByAnnotationClass('Doctrine\\Common\\Annotations\\Annotation\\NamedArgumentConstructor');
    }
    private function hasSingleArrayParam(ClassMethod $classMethod) : bool
    {
        if (\count($classMethod->params) !== 1) {
            return \false;
        }
        $onlyParam = $classMethod->params[0];
        // change array to properites
        if (!$onlyParam->type instanceof Node) {
            return \false;
        }
        $paramType = $this->nodeTypeResolver->getType($onlyParam);
        // we have a match
        return $paramType instanceof ArrayType;
    }
    /**
     * @param AssignToPropertyFetch[] $assignsToPropertyFetch
     */
    private function replaceAssignsByParam(array $assignsToPropertyFetch) : void
    {
        foreach ($assignsToPropertyFetch as $assignToPropertyFetch) {
            $assign = $assignToPropertyFetch->getAssign();
            $assign->expr = new Variable($assignToPropertyFetch->getPropertyName());
        }
    }
    /**
     * @return \PhpParser\Node\Stmt\Class_|null
     */
    private function decorateClassWithAssignClassMethod(Class_ $class)
    {
        // complete public properties
        $constructClassMethod = $this->constructClassMethodFactory->createFromPublicClassProperties($class);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        $this->classInsertManipulator->addAsFirstMethod($class, $constructClassMethod);
        return $class;
    }
}
