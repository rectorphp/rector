<?php

declare(strict_types=1);

namespace Rector\Sensio\Rector\FrameworkExtraBundle;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Sensio\NodeFactory\ThisRenderFactory;
use Rector\Sensio\TypeAnalyzer\ArrayUnionResponseTypeAnalyzer;
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
     * @var string
     */
    private const RESPONSE_CLASS = 'Symfony\Component\HttpFoundation\Response';

    /**
     * @var ReturnTypeDeclarationUpdater
     */
    private $returnTypeDeclarationUpdater;

    /**
     * @var ThisRenderFactory
     */
    private $thisRenderFactory;

    /**
     * @var ArrayUnionResponseTypeAnalyzer
     */
    private $arrayUnionResponseTypeAnalyzer;

    public function __construct(
        ReturnTypeDeclarationUpdater $returnTypeDeclarationUpdater,
        ThisRenderFactory $thisRenderFactory,
        ArrayUnionResponseTypeAnalyzer $arrayUnionResponseTypeAnalyzer
    ) {
        $this->returnTypeDeclarationUpdater = $returnTypeDeclarationUpdater;
        $this->thisRenderFactory = $thisRenderFactory;
        $this->arrayUnionResponseTypeAnalyzer = $arrayUnionResponseTypeAnalyzer;
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
    return $this->render('index.html.twig');
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

    private function addAbstractControllerParentClassIfMissing(Class_ $class): ?Class_
    {
        if ($class->extends !== null) {
            return null;
        }

        if (! $this->classHasTemplateAnnotations($class)) {
            return null;
        }

        $class->extends = new FullyQualified('Symfony\Bundle\FrameworkBundle\Controller\AbstractController');

        return $class;
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

        $this->refactorClassMethod($classMethod, $sensioTemplateTagValueNode);

        return $classMethod;
    }

    private function classHasTemplateAnnotations(Class_ $class): bool
    {
        foreach ($class->getMethods() as $classMethod) {
            if ($this->hasPhpDocTagValueNode($classMethod, SensioTemplateTagValueNode::class)) {
                return true;
            }
        }

        return false;
    }

    private function refactorClassMethod(
        ClassMethod $classMethod,
        SensioTemplateTagValueNode $sensioTemplateTagValueNode
    ): void {
        $hasThisRenderOrReturnsResponse = $this->hasLastReturnResponse($classMethod);

        /** @var Return_|null $lastReturn */
        $lastReturn = $this->betterNodeFinder->findLastInstanceOf((array) $classMethod->stmts, Return_::class);

        // nothing we can do
        if ($lastReturn !== null && $lastReturn->expr === null) {
            return;
        }

        // create "$this->render('template.file.twig.html', ['key' => 'value']);" method call
        $thisRenderMethodCall = $this->thisRenderFactory->create(
            $classMethod,
            $lastReturn,
            $sensioTemplateTagValueNode
        );

        if ($lastReturn === null) {
            $this->refactorNoReturn($classMethod, $thisRenderMethodCall);
            return;
        }

        $this->refactorReturnWithValue(
            $lastReturn,
            $hasThisRenderOrReturnsResponse,
            $thisRenderMethodCall,
            $classMethod
        );
    }

    private function processClassMethodWithoutReturn(
        ClassMethod $classMethod,
        MethodCall $thisRenderMethodCall
    ): void {
        $classMethod->stmts[] = new Return_($thisRenderMethodCall);
    }

    private function processIsArrayOrResponseType(
        Return_ $return,
        Expr $returnExpr,
        MethodCall $thisRenderMethodCall
    ): void {
        $this->removeNode($return);

        // create instance of Response → return response, or return $this->render
        $responseVariable = new Variable('response');

        $assign = new Assign($responseVariable, $returnExpr);

        $if = new If_(new Instanceof_($responseVariable, new FullyQualified(self::RESPONSE_CLASS)));
        $if->stmts[] = new Return_($responseVariable);

        $returnThisRender = new Return_($thisRenderMethodCall);
        $this->addNodesAfterNode([$assign, $if, $returnThisRender], $return);
    }

    private function hasLastReturnResponse(ClassMethod $classMethod): bool
    {
        /** @var Return_|null $lastReturn */
        $lastReturn = $this->betterNodeFinder->findLastInstanceOf((array) $classMethod->stmts, Return_::class);
        if ($lastReturn === null) {
            return false;
        }

        return $this->isReturnOfObjectType($lastReturn, self::RESPONSE_CLASS);
    }

    private function refactorNoReturn(ClassMethod $classMethod, MethodCall $thisRenderMethodCall): void
    {
        $this->processClassMethodWithoutReturn($classMethod, $thisRenderMethodCall);

        $this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, self::RESPONSE_CLASS);

        $this->removePhpDocTagValueNode($classMethod, SensioTemplateTagValueNode::class);
    }

    private function refactorReturnWithValue(
        Return_ $lastReturn,
        bool $hasThisRenderOrReturnsResponse,
        MethodCall $thisRenderMethodCall,
        ClassMethod $classMethod
    ): void {
        /** @var Expr $lastReturnExpr */
        $lastReturnExpr = $lastReturn->expr;

        $returnStaticType = $this->getStaticType($lastReturnExpr);

        if (! $lastReturn->expr instanceof MethodCall) {
            if (! $hasThisRenderOrReturnsResponse) {
                $lastReturn->expr = $thisRenderMethodCall;
            }
        } elseif ($returnStaticType instanceof ArrayType) {
            $lastReturn->expr = $thisRenderMethodCall;
        } elseif ($returnStaticType instanceof MixedType) {
            // nothing we can do
            return;
        }

        $isArrayOrResponseType = $this->arrayUnionResponseTypeAnalyzer->isArrayUnionResponseType(
            $returnStaticType,
            self::RESPONSE_CLASS
        );

        if ($isArrayOrResponseType) {
            $this->processIsArrayOrResponseType($lastReturn, $lastReturnExpr, $thisRenderMethodCall);
        }

        $this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, self::RESPONSE_CLASS);
        $this->removePhpDocTagValueNode($classMethod, SensioTemplateTagValueNode::class);
    }
}
