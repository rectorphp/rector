<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/doctrine/annotations/blob/1.13.x/docs/en/custom.rst#optional-constructors-with-named-parameters
 *
 * @see \Rector\Doctrine\Tests\Rector\Class_\ClassAnnotationToNamedArgumentConstructorRector\ClassAnnotationToNamedArgumentConstructorRectorTest
 */
final class ClassAnnotationToNamedArgumentConstructorRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Decorate classic array-based class annotation with named parameters', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
/**
 * @Annotation
 * @\Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor
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
     * @return array<class-string<\PhpParser\Node>>
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
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if ($phpDocInfo === null) {
            return null;
        }
        if (!$phpDocInfo->hasByNames(['annotation', 'Annotation'])) {
            return null;
        }
        if ($phpDocInfo->hasByAnnotationClass('Doctrine\\Common\\Annotations\\Annotation\\NamedArgumentConstructor')) {
            return null;
        }
        $doctrineAnnotationTagValueNode = new \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode('Doctrine\\Common\\Annotations\\Annotation\\NamedArgumentConstructor');
        $phpDocInfo->addTagValueNode($doctrineAnnotationTagValueNode);
        $classMethod = $node->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        if (\count($classMethod->params) !== 1) {
            return null;
        }
        $onlyParam = $classMethod->params[0];
        // change array to properites
        if ($onlyParam->type) {
            $paramType = $this->nodeTypeResolver->getStaticType($onlyParam);
            // we have a match
            if (!$paramType instanceof \PHPStan\Type\ArrayType) {
                return null;
            }
        }
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf($node->stmts, \PhpParser\Node\Expr\Assign::class);
        $params = [];
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                continue;
            }
            // decorate property fetches to params
            $propertyFetch = $assign->var;
            $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
            if ($propertyName === null) {
                continue;
            }
            $variable = new \PhpParser\Node\Expr\Variable($propertyName);
            $params[] = $this->createParam($propertyFetch, $variable);
            $assign->expr = $variable;
        }
        $classMethod->params = $params;
        return $node;
    }
    private function createParam(\PhpParser\Node\Expr\PropertyFetch $propertyFetch, \PhpParser\Node\Expr\Variable $variable) : \PhpParser\Node\Param
    {
        $param = new \PhpParser\Node\Param($variable);
        $paramType = $this->nodeTypeResolver->getStaticType($propertyFetch);
        $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramType);
        return $param;
    }
}
