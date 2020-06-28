<?php

declare(strict_types=1);

namespace Rector\Sensio\Rector\FrameworkExtraBundle;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Sensio\NodeFactory\ArrayFromCompactFactory;
use Rector\Sensio\NodeFactory\ThisRenderFactory;
use Rector\Sensio\TypeDeclaration\ReturnTypeDeclarationUpdater;

/**
 * @see https://github.com/symfony/symfony-docs/pull/12387#discussion_r329551967
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/view.html
 * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/issues/641
 *
 * @see \Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationToThisRenderRector\TemplateAnnotationToThisRenderRectorTest
 */
final class TemplateAnnotationToThisRenderRector extends AbstractRector
{
    /**
     * @var ArrayFromCompactFactory
     */
    private $arrayFromCompactFactory;

    /**
     * @var ReturnTypeDeclarationUpdater
     */
    private $returnTypeDeclarationUpdater;

    /**
     * @var ThisRenderFactory
     */
    private $thisRenderFactory;

    public function __construct(
        ArrayFromCompactFactory $arrayFromCompactFactory,
        ReturnTypeDeclarationUpdater $returnTypeDeclarationUpdater,
        ThisRenderFactory $thisRenderFactory
    ) {
        $this->arrayFromCompactFactory = $arrayFromCompactFactory;
        $this->returnTypeDeclarationUpdater = $returnTypeDeclarationUpdater;
        $this->thisRenderFactory = $thisRenderFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony',
            [
                new CodeSample(
                    <<<'PHP'
/**
 * @Template()
 */
public function indexAction()
{
}
PHP
                    ,
                    <<<'PHP'
public function indexAction()
{
    return $this->render("index.html.twig");
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Class_) {
            return $this->addAbstractControllerParentClassIfMissing($node);
        }

        if ($node instanceof ClassMethod) {
            return $this->replaceTemplateAnnotation($node);
        }

        return null;
    }

    private function addAbstractControllerParentClassIfMissing(Class_ $node): ?Class_
    {
        if ($node->extends !== null) {
            return null;
        }

        if (! $this->classHasTemplateAnnotations($node)) {
            return null;
        }

        $node->extends = new FullyQualified('Symfony\Bundle\FrameworkBundle\Controller\AbstractController');

        return $node;
    }

    private function replaceTemplateAnnotation(ClassMethod $classMethod): ?Node
    {
        if (! $classMethod->isPublic()) {
            return null;
        }

        /** @var SensioTemplateTagValueNode|null $sensioTemplateTagValueNode */
        $sensioTemplateTagValueNode = $this->getPhpDocTagValueNode($classMethod, SensioTemplateTagValueNode::class);
        if ($sensioTemplateTagValueNode === null) {
            return null;
        }

        $this->returnTypeDeclarationUpdater->updateClassMethod(
            $classMethod,
            'Symfony\Component\HttpFoundation\Response'
        );

        $this->refactorClassMethod($classMethod, $sensioTemplateTagValueNode);

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->removeByType(SensioTemplateTagValueNode::class);

        return $classMethod;
    }

    private function classHasTemplateAnnotations(Class_ $class): bool
    {
        foreach ($class->getMethods() as $classMethod) {
            /** @var PhpDocInfo|null $phpDocInfo */
            $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($phpDocInfo === null) {
                continue;
            }

            if ($phpDocInfo->hasByType(SensioTemplateTagValueNode::class)) {
                return true;
            }
        }

        return false;
    }

    private function updateReturnType(ClassMethod $classMethod): void
    {
        $this->returnTypeDeclarationUpdater->updateClassMethod(
            $classMethod,
            'Symfony\Component\HttpFoundation\Response'
        );
    }

    private function refactorClassMethod(
        ClassMethod $classMethod,
        SensioTemplateTagValueNode $sensioTemplateTagValueNode
    ): void {
        /** @var Return_|null $returnNode */
        $returnNode = $this->betterNodeFinder->findLastInstanceOf((array) $classMethod->stmts, Return_::class);

        if ($returnNode !== null && $returnNode->expr instanceof MethodCall) {
            // go inside called method
            $innerClassMethod = $this->functionLikeParsedNodesFinder->findClassMethodByMethodCall($returnNode->expr);
            if ($innerClassMethod !== null) {
                $this->refactorClassMethod($innerClassMethod, $sensioTemplateTagValueNode);

                return;
            }
        }

        // create "$this->render('template.file.twig.html', ['key' => 'value']);" method call
        $thisRenderMethodCall = $this->thisRenderFactory->create(
            $classMethod,
            $returnNode,
            $sensioTemplateTagValueNode
        );

        if ($returnNode === null) {
            // or add as last statement in the method
            $classMethod->stmts[] = new Return_($thisRenderMethodCall);
        } elseif ($returnNode->expr !== null) {
            if ($this->isFuncCallName($returnNode->expr, 'compact')) {
                /** @var FuncCall $compactFunCall */
                $compactFunCall = $returnNode->expr;

                $array = $this->arrayFromCompactFactory->createArrayFromCompactFuncCall($compactFunCall);
                $thisRenderMethodCall->args[1] = new Arg($array);
                $returnNode->expr = $thisRenderMethodCall;
            } elseif (! $returnNode->expr instanceof MethodCall) {
                $returnNode->expr = $thisRenderMethodCall;
            }
        }

        // replace Return_ node value if exists and is not already in correct format

        $this->updateReturnType($classMethod);
    }
}
