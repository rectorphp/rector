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
     * @var NodeFactory
     */
    private $nodeFactory;
    /**
     * @var TemplateGuesser
     */
    private $templateGuesser;
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var ArrayFromCompactFactory
     */
    private $arrayFromCompactFactory;
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\Symfony\NodeFactory\ArrayFromCompactFactory $arrayFromCompactFactory, NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, TemplateGuesser $templateGuesser)
    {
        $this->nodeFactory = $nodeFactory;
        $this->templateGuesser = $templateGuesser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayFromCompactFactory = $arrayFromCompactFactory;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function create(ClassMethod $classMethod, ?Return_ $return, DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode) : MethodCall
    {
        $renderArguments = $this->resolveRenderArguments($classMethod, $return, $templateDoctrineAnnotationTagValueNode);
        return $this->nodeFactory->createMethodCall('this', 'render', $renderArguments);
    }
    /**
     * @return Arg[]
     */
    private function resolveRenderArguments(ClassMethod $classMethod, ?Return_ $return, DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode) : array
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
        $template = $templateDoctrineAnnotationTagValueNode->getValue('template') ?: $templateDoctrineAnnotationTagValueNode->getSilentValue();
        if ($template !== null) {
            return $template;
        }
        return $this->templateGuesser->resolveFromClassMethodNode($classMethod);
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
        if ($return->expr instanceof Array_ && \count($return->expr->items)) {
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
        $returnStaticType = $this->nodeTypeResolver->getStaticType($methodCall);
        if ($returnStaticType instanceof ArrayType) {
            return $methodCall;
        }
        return null;
    }
}
