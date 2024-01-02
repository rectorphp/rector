<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Class_;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/constructor_promotion
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\DowngradePropertyPromotionRectorTest
 */
final class DowngradePropertyPromotionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, BetterStandardPrinter $betterStandardPrinter, PhpDocInfoFactory $phpDocInfoFactory, BetterNodeFinder $betterNodeFinder)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change constructor property promotion to property assign', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $constructorClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return null;
        }
        $oldComments = $this->getOldComments($constructorClassMethod);
        $promotedParams = $this->resolvePromotedParams($constructorClassMethod);
        if ($promotedParams === []) {
            return null;
        }
        $properties = $this->resolvePropertiesFromPromotedParams($constructorClassMethod, $promotedParams, $node);
        $this->addPropertyAssignsToConstructorClassMethod($properties, $node, $oldComments);
        foreach ($promotedParams as $promotedParam) {
            $promotedParam->flags = 0;
        }
        return $node;
    }
    /**
     * @return array<string, Comment|null>
     */
    private function getOldComments(ClassMethod $constructorClassMethod) : array
    {
        $oldComments = [];
        foreach ($constructorClassMethod->params as $param) {
            $oldComments[$this->getName($param->var)] = $param->getAttribute(AttributeKey::COMMENTS);
        }
        return $oldComments;
    }
    /**
     * @return Param[]
     */
    private function resolvePromotedParams(ClassMethod $constructorClassMethod) : array
    {
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
    private function setParamAttrGroupAsComment(Param $param) : void
    {
        $attrGroupsPrint = $this->betterStandardPrinter->print($param->attrGroups);
        $comments = $param->getAttribute(AttributeKey::COMMENTS);
        if (\is_array($comments)) {
            /** @var Comment[] $comments */
            foreach ($comments as $comment) {
                $attrGroupsPrint = \str_replace($comment->getText(), '', $attrGroupsPrint);
            }
        }
        $comments = $param->attrGroups !== [] ? [new Comment($attrGroupsPrint)] : null;
        $param->attrGroups = [];
        $param->setAttribute(AttributeKey::COMMENTS, $comments);
    }
    /**
     * @param Param[] $promotedParams
     * @return Property[]
     */
    private function resolvePropertiesFromPromotedParams(ClassMethod $classMethod, array $promotedParams, Class_ $class) : array
    {
        $properties = $this->createPropertiesFromParams($classMethod, $promotedParams);
        $class->stmts = \array_merge($properties, $class->stmts);
        return $properties;
    }
    /**
     * @param Property[] $properties
     * @param array<string, Comment|null> $oldComments
     */
    private function addPropertyAssignsToConstructorClassMethod(array $properties, Class_ $class, array $oldComments) : void
    {
        $assigns = [];
        foreach ($properties as $property) {
            $propertyName = $this->getName($property);
            $assign = $this->nodeFactory->createPropertyAssignment($propertyName);
            $expression = new Expression($assign);
            $expression->setAttribute(AttributeKey::COMMENTS, $oldComments[$propertyName]);
            $assigns[] = $expression;
        }
        /** @var ClassMethod $constructorClassMethod */
        $constructorClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        $constructorClassMethod->stmts = \array_merge($assigns, (array) $constructorClassMethod->stmts);
    }
    /**
     * @param Param[] $params
     * @return Property[]
     */
    private function createPropertiesFromParams(ClassMethod $classMethod, array $params) : array
    {
        $properties = [];
        foreach ($params as $param) {
            /** @var string $name */
            $name = $this->getName($param->var);
            $property = new Property($param->flags, [new PropertyProperty($name)], [], $param->type);
            $this->decoratePropertyWithParamDocInfo($classMethod, $param, $property);
            $hasNew = $param->default instanceof Expr && (bool) $this->betterNodeFinder->findFirstInstanceOf($param->default, New_::class);
            if ($param->default instanceof Expr && !$hasNew) {
                $property->props[0]->default = $param->default;
            }
            $properties[] = $property;
        }
        return $properties;
    }
    private function decoratePropertyWithParamDocInfo(ClassMethod $constructorClassMethod, Param $param, Property $property) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($constructorClassMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $name = $this->getName($param->var);
        if ($name === null) {
            return;
        }
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($name);
        if (!$paramTagValueNode instanceof ParamTagValueNode) {
            return;
        }
        $propertyDocInfo = $this->phpDocInfoFactory->createEmpty($property);
        $this->phpDocTypeChanger->changeVarTypeNode($property, $propertyDocInfo, $paramTagValueNode->type);
    }
}
