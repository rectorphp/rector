<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Class_;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/constructor_promotion
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\DowngradePropertyPromotionRectorTest
 */
final class DowngradePropertyPromotionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    public function __construct(\Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->classInsertManipulator = $classInsertManipulator;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change constructor property promotion to property assign', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(public float $value = 0.0)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public float $value;

    public function __construct(float $value = 0.0)
    {
        $this->value = $value;
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $oldComments = $this->getOldComments($node);
        $promotedParams = $this->resolvePromotedParams($node);
        if ($promotedParams === []) {
            return null;
        }
        $properties = $this->resolvePropertiesFromPromotedParams($promotedParams, $node);
        $this->addPropertyAssignsToConstructorClassMethod($properties, $node, $oldComments);
        foreach ($promotedParams as $promotedParam) {
            $promotedParam->flags = 0;
        }
        return $node;
    }
    /**
     * @return array<string, Comment|null>
     */
    private function getOldComments(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $constructorClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return [];
        }
        $oldComments = [];
        foreach ($constructorClassMethod->params as $param) {
            $oldComments[$this->getName($param->var)] = $param->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS);
        }
        return $oldComments;
    }
    /**
     * @return Param[]
     */
    private function resolvePromotedParams(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $constructorClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return [];
        }
        $promotedParams = [];
        foreach ($constructorClassMethod->params as $param) {
            if ($param->flags === 0) {
                continue;
            }
            $this->setParamAttrGroupAsComment($param);
            $promotedParams[] = $param;
        }
        return $promotedParams;
    }
    private function setParamAttrGroupAsComment(\PhpParser\Node\Param $param) : void
    {
        $attrGroupsPrint = $this->betterStandardPrinter->print($param->attrGroups);
        $comments = $param->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS);
        if (\is_array($comments)) {
            /** @var Comment[] $comments */
            foreach ($comments as $comment) {
                $attrGroupsPrint = \str_replace($comment->getText(), '', $attrGroupsPrint);
            }
        }
        $comments = $param->attrGroups !== [] ? [new \PhpParser\Comment($attrGroupsPrint)] : null;
        $param->attrGroups = [];
        $param->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, $comments);
    }
    /**
     * @param Param[] $promotedParams
     * @return Property[]
     */
    private function resolvePropertiesFromPromotedParams(array $promotedParams, \PhpParser\Node\Stmt\Class_ $class) : array
    {
        $properties = $this->createPropertiesFromParams($promotedParams);
        $this->classInsertManipulator->addPropertiesToClass($class, $properties);
        return $properties;
    }
    /**
     * @param Property[] $properties
     * @param array<string, Comment|null> $oldComments
     */
    private function addPropertyAssignsToConstructorClassMethod(array $properties, \PhpParser\Node\Stmt\Class_ $class, array $oldComments) : void
    {
        $assigns = [];
        foreach ($properties as $property) {
            $propertyName = $this->getName($property);
            $assign = $this->nodeFactory->createPropertyAssignment($propertyName);
            $expression = new \PhpParser\Node\Stmt\Expression($assign);
            $expression->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, $oldComments[$propertyName]);
            $assigns[] = $expression;
        }
        /** @var ClassMethod $constructorClassMethod */
        $constructorClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        $constructorClassMethod->stmts = \array_merge($assigns, (array) $constructorClassMethod->stmts);
    }
    /**
     * @param Param[] $params
     * @return Property[]
     */
    private function createPropertiesFromParams(array $params) : array
    {
        $properties = [];
        foreach ($params as $param) {
            /** @var string $name */
            $name = $this->getName($param->var);
            $property = $this->nodeFactory->createProperty($name);
            $property->flags = $param->flags;
            $property->type = $param->type;
            $this->decoratePropertyWithParamDocInfo($param, $property);
            $hasNew = $param->default === null ? \false : (bool) $this->betterNodeFinder->findFirstInstanceOf($param->default, \PhpParser\Node\Expr\New_::class);
            if ($param->default !== null && !$hasNew) {
                $property->props[0]->default = $param->default;
            }
            $properties[] = $property;
        }
        return $properties;
    }
    private function decoratePropertyWithParamDocInfo(\PhpParser\Node\Param $param, \PhpParser\Node\Stmt\Property $property) : void
    {
        $constructorClassMethod = $this->betterNodeFinder->findParentType($param, \PhpParser\Node\Stmt\ClassMethod::class);
        if (!$constructorClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($constructorClassMethod);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return;
        }
        $name = $this->getName($param->var);
        if ($name === null) {
            return;
        }
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($name);
        if (!$paramTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode) {
            return;
        }
        $propertyDocInfo = $this->phpDocInfoFactory->createEmpty($property);
        $this->phpDocTypeChanger->changeVarTypeNode($propertyDocInfo, $paramTagValueNode->type);
    }
}
