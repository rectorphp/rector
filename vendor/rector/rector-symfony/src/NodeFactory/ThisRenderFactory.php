<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
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
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Symfony\Helper\TemplateGuesser;
final class ThisRenderFactory
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\ArrayFromCompactFactory
     */
    private $arrayFromCompactFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\Helper\TemplateGuesser
     */
    private $templateGuesser;
    public function __construct(\Rector\Symfony\NodeFactory\ArrayFromCompactFactory $arrayFromCompactFactory, NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, TemplateGuesser $templateGuesser)
    {
        $this->arrayFromCompactFactory = $arrayFromCompactFactory;
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->templateGuesser = $templateGuesser;
    }
    public function create(?Return_ $return, DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode, ClassMethod $classMethod) : MethodCall
    {
        $renderArguments = $this->resolveRenderArguments($return, $templateDoctrineAnnotationTagValueNode, $classMethod);
        return $this->nodeFactory->createMethodCall('this', 'render', $renderArguments);
    }
    /**
     * @return Arg[]
     */
    private function resolveRenderArguments(?Return_ $return, DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode, ClassMethod $classMethod) : array
    {
        $templateNameString = $this->resolveTemplateName($classMethod, $templateDoctrineAnnotationTagValueNode);
        $arguments = [$templateNameString];
        $parametersExpr = $this->resolveParametersExpr($return, $templateDoctrineAnnotationTagValueNode);
        if ($parametersExpr instanceof Expr) {
            $arguments[] = new Arg($parametersExpr);
        }
        return $this->nodeFactory->createArgs($arguments);
    }
    private function resolveTemplateName(ClassMethod $classMethod, DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode) : string
    {
        $template = $this->resolveTemplate($templateDoctrineAnnotationTagValueNode);
        if (\is_string($template)) {
            return $template;
        }
        return $this->templateGuesser->resolveFromClassMethod($classMethod);
    }
    private function resolveParametersExpr(?Return_ $return, DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode) : ?Expr
    {
        $vars = [];
        $varsArrayItemNode = $templateDoctrineAnnotationTagValueNode->getValue('vars');
        if ($varsArrayItemNode instanceof ArrayItemNode && $varsArrayItemNode->value instanceof CurlyListNode) {
            $vars = $varsArrayItemNode->value->getValues();
        }
        if ($vars !== []) {
            return $this->createArrayFromArrayItemNodes($vars);
        }
        if (!$return instanceof Return_) {
            return null;
        }
        if ($return->expr instanceof Array_ && $return->expr->items !== []) {
            return $return->expr;
        }
        if ($return->expr instanceof MethodCall) {
            return $this->resolveMethodCall($return->expr);
        }
        if ($return->expr instanceof FuncCall && $this->nodeNameResolver->isName($return->expr, 'compact')) {
            $compactFunCall = $return->expr;
            return $this->arrayFromCompactFactory->createArrayFromCompactFuncCall($compactFunCall);
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
    private function resolveTemplate(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : ?string
    {
        $templateParameter = $doctrineAnnotationTagValueNode->getValue('template');
        if ($templateParameter instanceof ArrayItemNode) {
            $templateParameterValue = $templateParameter->value;
            if ($templateParameterValue instanceof StringNode) {
                $templateParameterValue = $templateParameterValue->value;
            }
            if (\is_string($templateParameterValue)) {
                return $templateParameterValue;
            }
        }
        $arrayItemNode = $doctrineAnnotationTagValueNode->getSilentValue();
        if ($arrayItemNode instanceof ArrayItemNode) {
            $arrayItemNodeValue = $arrayItemNode->value;
            if ($arrayItemNodeValue instanceof StringNode) {
                $arrayItemNodeValue = $arrayItemNodeValue->value;
            }
            if (\is_string($arrayItemNodeValue)) {
                return $arrayItemNodeValue;
            }
        }
        return null;
    }
}
