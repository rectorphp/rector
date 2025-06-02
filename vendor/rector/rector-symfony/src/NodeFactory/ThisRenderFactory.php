<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ArrayType;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\NodeFactory;
use Rector\Symfony\Helper\TemplateGuesser;
use Rector\Symfony\NodeFactory\Annotations\AnnotationOrAttributeValueResolver;
final class ThisRenderFactory
{
    /**
     * @readonly
     */
    private \Rector\Symfony\NodeFactory\ArrayFromCompactFactory $arrayFromCompactFactory;
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private TemplateGuesser $templateGuesser;
    /**
     * @readonly
     */
    private AnnotationOrAttributeValueResolver $annotationOrAttributeValueResolver;
    public function __construct(\Rector\Symfony\NodeFactory\ArrayFromCompactFactory $arrayFromCompactFactory, NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, TemplateGuesser $templateGuesser, AnnotationOrAttributeValueResolver $annotationOrAttributeValueResolver)
    {
        $this->arrayFromCompactFactory = $arrayFromCompactFactory;
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->templateGuesser = $templateGuesser;
        $this->annotationOrAttributeValueResolver = $annotationOrAttributeValueResolver;
    }
    /**
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute $templateTagValueNodeOrAttribute
     */
    public function create(?Return_ $return, $templateTagValueNodeOrAttribute, ClassMethod $classMethod) : MethodCall
    {
        $renderArguments = $this->resolveRenderArguments($return, $templateTagValueNodeOrAttribute, $classMethod);
        return $this->nodeFactory->createMethodCall('this', 'render', $renderArguments);
    }
    /**
     * @return Arg[]
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute $templateTagValueNodeOrAttribute
     */
    private function resolveRenderArguments(?Return_ $return, $templateTagValueNodeOrAttribute, ClassMethod $classMethod) : array
    {
        $templateNameString = $this->resolveTemplateName($classMethod, $templateTagValueNodeOrAttribute);
        $arguments = [$templateNameString];
        $parametersExpr = $this->resolveParametersExpr($return, $templateTagValueNodeOrAttribute);
        if ($parametersExpr instanceof Expr) {
            $arguments[] = new Arg($parametersExpr);
        }
        return $this->nodeFactory->createArgs($arguments);
    }
    /**
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute $templateTagValueNodeOrAttribute
     */
    private function resolveTemplateName(ClassMethod $classMethod, $templateTagValueNodeOrAttribute) : string
    {
        $template = $this->annotationOrAttributeValueResolver->resolve($templateTagValueNodeOrAttribute, 'template');
        if (\is_string($template)) {
            return $template;
        }
        return $this->templateGuesser->resolveFromClassMethod($classMethod);
    }
    /**
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute $templateTagValueNodeOrAttribute
     */
    private function resolveParametersExpr(?Return_ $return, $templateTagValueNodeOrAttribute) : ?Expr
    {
        $vars = [];
        if ($templateTagValueNodeOrAttribute instanceof DoctrineAnnotationTagValueNode) {
            $varsArrayItemNode = $templateTagValueNodeOrAttribute->getValue('vars');
            if ($varsArrayItemNode instanceof ArrayItemNode && $varsArrayItemNode->value instanceof CurlyListNode) {
                $vars = $varsArrayItemNode->value->getValues();
            }
        } else {
            foreach ($templateTagValueNodeOrAttribute->args as $arg) {
                if ($arg->name !== null && $this->nodeNameResolver->isName($arg->name, 'vars')) {
                    // @todo might need more work
                    $vars = $arg->value;
                }
            }
        }
        if ($vars !== []) {
            return $this->createArrayFromArrayItemNodes($vars);
        }
        if (!$return instanceof Return_) {
            return null;
        }
        if (!$return->expr instanceof Expr) {
            return null;
        }
        $returnExprType = $this->nodeTypeResolver->getType($return->expr);
        if ($return->expr instanceof Array_) {
            $array = $return->expr;
            // no point in returning empty items
            if ($array->items === []) {
                return null;
            }
            return $return->expr;
        }
        if ($return->expr instanceof MethodCall) {
            return $this->resolveMethodCall($return->expr);
        }
        if ($return->expr instanceof FuncCall && $this->nodeNameResolver->isName($return->expr, 'compact')) {
            $compactFunCall = $return->expr;
            return $this->arrayFromCompactFactory->createArrayFromCompactFuncCall($compactFunCall);
        }
        if ($returnExprType->isArray()->yes()) {
            return $return->expr;
        }
        return null;
    }
    /**
     * @param ArrayItemNode[] $arrayItemNodes
     */
    private function createArrayFromArrayItemNodes(array $arrayItemNodes) : Array_
    {
        $arrayItems = [];
        foreach ($arrayItemNodes as $arrayItemNode) {
            $arrayItemNodeValue = $arrayItemNode->value;
            if ($arrayItemNodeValue instanceof StringNode) {
                $arrayItemNodeValue = $arrayItemNodeValue->value;
            }
            $arrayItems[] = new ArrayItem(new Variable($arrayItemNodeValue), new String_($arrayItemNodeValue));
        }
        return new Array_($arrayItems);
    }
    private function resolveMethodCall(MethodCall $methodCall) : ?Expr
    {
        $returnStaticType = $this->nodeTypeResolver->getType($methodCall);
        if ($returnStaticType instanceof ArrayType) {
            return $methodCall;
        }
        return null;
    }
}
