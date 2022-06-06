<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20220606\Rector\Symfony\Helper\TemplateGuesser;
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
    public function __construct(ArrayFromCompactFactory $arrayFromCompactFactory, NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, TemplateGuesser $templateGuesser)
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
        if ($parametersExpr !== null) {
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
        $vars = $templateDoctrineAnnotationTagValueNode->getValue('vars');
        if ($vars instanceof CurlyListNode) {
            $vars = $vars->getValuesWithExplicitSilentAndWithoutQuotes();
        }
        if (\is_array($vars) && $vars !== []) {
            return $this->createArrayFromVars($vars);
        }
        if ($return === null) {
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
     * @param string[] $vars
     */
    private function createArrayFromVars(array $vars) : Array_
    {
        $arrayItems = [];
        foreach ($vars as $var) {
            $arrayItems[] = new ArrayItem(new Variable($var), new String_($var));
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
    /**
     * @return string|null
     */
    private function resolveTemplate(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
    {
        $templateParameter = $doctrineAnnotationTagValueNode->getValue('template');
        if (\is_string($templateParameter)) {
            return $templateParameter;
        }
        $silentValue = $doctrineAnnotationTagValueNode->getSilentValue();
        if (\is_string($silentValue)) {
            return $silentValue;
        }
        return null;
    }
}
