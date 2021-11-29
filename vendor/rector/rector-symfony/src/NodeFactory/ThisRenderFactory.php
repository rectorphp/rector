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
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Symfony\Helper\TemplateGuesser;
final class ThisRenderFactory
{
    /**
     * @var \Rector\Symfony\NodeFactory\ArrayFromCompactFactory
     */
    private $arrayFromCompactFactory;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\Symfony\Helper\TemplateGuesser
     */
    private $templateGuesser;
    public function __construct(\Rector\Symfony\NodeFactory\ArrayFromCompactFactory $arrayFromCompactFactory, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Symfony\Helper\TemplateGuesser $templateGuesser)
    {
        $this->arrayFromCompactFactory = $arrayFromCompactFactory;
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->templateGuesser = $templateGuesser;
    }
    public function create(\PhpParser\Node\Stmt\ClassMethod $classMethod, ?\PhpParser\Node\Stmt\Return_ $return, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode) : \PhpParser\Node\Expr\MethodCall
    {
        $renderArguments = $this->resolveRenderArguments($classMethod, $return, $templateDoctrineAnnotationTagValueNode);
        return $this->nodeFactory->createMethodCall('this', 'render', $renderArguments);
    }
    /**
     * @return Arg[]
     */
    private function resolveRenderArguments(\PhpParser\Node\Stmt\ClassMethod $classMethod, ?\PhpParser\Node\Stmt\Return_ $return, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode) : array
    {
        $templateNameString = $this->resolveTemplateName($classMethod, $templateDoctrineAnnotationTagValueNode);
        $arguments = [$templateNameString];
        $parametersExpr = $this->resolveParametersExpr($return, $templateDoctrineAnnotationTagValueNode);
        if ($parametersExpr !== null) {
            $arguments[] = new \PhpParser\Node\Arg($parametersExpr);
        }
        return $this->nodeFactory->createArgs($arguments);
    }
    private function resolveTemplateName(\PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode) : string
    {
        $template = $this->resolveTemplate($templateDoctrineAnnotationTagValueNode);
        if (\is_string($template)) {
            return $template;
        }
        return $this->templateGuesser->resolveFromClassMethodNode($classMethod);
    }
    private function resolveParametersExpr(?\PhpParser\Node\Stmt\Return_ $return, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode) : ?\PhpParser\Node\Expr
    {
        $vars = $templateDoctrineAnnotationTagValueNode->getValue('vars');
        if ($vars instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
            $vars = $vars->getValuesWithExplicitSilentAndWithoutQuotes();
        }
        if (\is_array($vars) && $vars !== []) {
            return $this->createArrayFromVars($vars);
        }
        if ($return === null) {
            return null;
        }
        if ($return->expr instanceof \PhpParser\Node\Expr\Array_ && $return->expr->items !== []) {
            return $return->expr;
        }
        if ($return->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->resolveMethodCall($return->expr);
        }
        if ($return->expr instanceof \PhpParser\Node\Expr\FuncCall && $this->nodeNameResolver->isName($return->expr, 'compact')) {
            $compactFunCall = $return->expr;
            return $this->arrayFromCompactFactory->createArrayFromCompactFuncCall($compactFunCall);
        }
        return null;
    }
    /**
     * @param string[] $vars
     */
    private function createArrayFromVars(array $vars) : \PhpParser\Node\Expr\Array_
    {
        $arrayItems = [];
        foreach ($vars as $var) {
            $arrayItems[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\Variable($var), new \PhpParser\Node\Scalar\String_($var));
        }
        return new \PhpParser\Node\Expr\Array_($arrayItems);
    }
    private function resolveMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr
    {
        $returnStaticType = $this->nodeTypeResolver->getType($methodCall);
        if ($returnStaticType instanceof \PHPStan\Type\ArrayType) {
            return $methodCall;
        }
        return null;
    }
    /**
     * @return string|null
     */
    private function resolveTemplate(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
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
