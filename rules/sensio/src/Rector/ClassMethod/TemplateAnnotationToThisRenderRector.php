<?php

declare(strict_types=1);

namespace Rector\Sensio\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Sensio\NodeFactory\ThisRenderFactory;
use Rector\Sensio\TypeAnalyzer\ArrayUnionResponseTypeAnalyzer;
use Rector\Sensio\TypeDeclaration\ReturnTypeDeclarationUpdater;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/symfony/symfony-docs/pull/12387#discussion_r329551967
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/view.html
 * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/issues/641
 *
 * @see \Rector\Sensio\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\TemplateAnnotationToThisRenderRectorTest
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
        ArrayUnionResponseTypeAnalyzer $arrayUnionResponseTypeAnalyzer,
        ReturnTypeDeclarationUpdater $returnTypeDeclarationUpdater,
        ThisRenderFactory $thisRenderFactory
    ) {
        $this->returnTypeDeclarationUpdater = $returnTypeDeclarationUpdater;
        $this->thisRenderFactory = $thisRenderFactory;
        $this->arrayUnionResponseTypeAnalyzer = $arrayUnionResponseTypeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
/**
 * @Template()
 */
public function indexAction()
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
public function indexAction()
{
    return $this->render('index.html.twig');
}
CODE_SAMPLE
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

    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Class_) {
            return $this->addAbstractControllerParentClassIfMissing($node);
        }

        return $this->replaceTemplateAnnotation($node);
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

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        $sensioTemplateTagValueNode = $phpDocInfo->getByType(SensioTemplateTagValueNode::class);
        if (! $sensioTemplateTagValueNode instanceof SensioTemplateTagValueNode) {
            return null;
        }

        $this->refactorClassMethod($classMethod, $sensioTemplateTagValueNode);

        return $classMethod;
    }

    private function classHasTemplateAnnotations(Class_ $class): bool
    {
        foreach ($class->getMethods() as $classMethod) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            if ($phpDocInfo->hasByType(SensioTemplateTagValueNode::class)) {
                return true;
            }
        }

        return false;
    }

    private function refactorClassMethod(
        ClassMethod $classMethod,
        SensioTemplateTagValueNode $sensioTemplateTagValueNode
    ): void {
        /** @var Return_[] $returns */
        $returns = $this->findReturnsInCurrentScope((array) $classMethod->stmts);

        $hasThisRenderOrReturnsResponse = $this->hasLastReturnResponse($classMethod);

        foreach ($returns as $return) {
            $this->refactorReturn($return, $classMethod, $sensioTemplateTagValueNode, $hasThisRenderOrReturnsResponse);
        }

        if ($returns === []) {
            $thisRenderMethodCall = $this->thisRenderFactory->create(
                $classMethod,
                null,
                $sensioTemplateTagValueNode
            );

            $this->refactorNoReturn($classMethod, $thisRenderMethodCall);
        }
    }

    /**
     * This skips anonymous functions and functions, as their returns doesn't influence current code
     *
     * @param Node[] $stmts
     * @return Return_[]
     */
    private function findReturnsInCurrentScope(array $stmts): array
    {
        $returns = [];
        $this->traverseNodesWithCallable($stmts, function (Node $node) use (&$returns): ?int {
            if ($node instanceof Closure) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($node instanceof Function_) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (! $node instanceof Return_) {
                return null;
            }

            $returns[] = $node;

            return null;
        });

        return $returns;
    }

    private function hasLastReturnResponse(ClassMethod $classMethod): bool
    {
        $lastReturn = $this->betterNodeFinder->findLastInstanceOf((array) $classMethod->stmts, Return_::class);
        if (! $lastReturn instanceof Return_) {
            return false;
        }

        return $this->isReturnOfObjectType($lastReturn, self::RESPONSE_CLASS);
    }

    private function isReturnOfObjectType(Return_ $return, string $objectType): bool
    {
        if ($return->expr === null) {
            return false;
        }

        $returnType = $this->getStaticType($return->expr);
        if (! $returnType instanceof TypeWithClassName) {
            return false;
        }

        return is_a($returnType->getClassName(), $objectType, true);
    }

    private function refactorReturn(
        Return_ $return,
        ClassMethod $classMethod,
        SensioTemplateTagValueNode $sensioTemplateTagValueNode,
        bool $hasThisRenderOrReturnsResponse
    ): void {
        // nothing we can do
        if ($return->expr === null) {
            return;
        }

        // create "$this->render('template.file.twig.html', ['key' => 'value']);" method call
        $thisRenderMethodCall = $this->thisRenderFactory->create(
            $classMethod,
            $return,
            $sensioTemplateTagValueNode
        );

        $this->refactorReturnWithValue(
            $return,
            $hasThisRenderOrReturnsResponse,
            $thisRenderMethodCall,
            $classMethod
        );
    }

    private function refactorNoReturn(ClassMethod $classMethod, MethodCall $thisRenderMethodCall): void
    {
        $this->processClassMethodWithoutReturn($classMethod, $thisRenderMethodCall);

        $this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, self::RESPONSE_CLASS);

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->removeByType(SensioTemplateTagValueNode::class);
    }

    private function refactorReturnWithValue(
        Return_ $return,
        bool $hasThisRenderOrReturnsResponse,
        MethodCall $thisRenderMethodCall,
        ClassMethod $classMethod
    ): void {
        /** @var Expr $lastReturnExpr */
        $lastReturnExpr = $return->expr;

        $returnStaticType = $this->getStaticType($lastReturnExpr);

        if (! $return->expr instanceof MethodCall) {
            if (! $hasThisRenderOrReturnsResponse || $returnStaticType instanceof ConstantArrayType) {
                $return->expr = $thisRenderMethodCall;
            }
        } elseif ($returnStaticType instanceof ArrayType) {
            $return->expr = $thisRenderMethodCall;
        } elseif ($returnStaticType instanceof MixedType) {
            // nothing we can do
            return;
        }

        $isArrayOrResponseType = $this->arrayUnionResponseTypeAnalyzer->isArrayUnionResponseType(
            $returnStaticType,
            self::RESPONSE_CLASS
        );

        if ($isArrayOrResponseType) {
            $this->processIsArrayOrResponseType($return, $lastReturnExpr, $thisRenderMethodCall);
        }

        $this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, self::RESPONSE_CLASS);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->removeByType(SensioTemplateTagValueNode::class);
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
        $responseVariable = new Variable('responseOrData');

        $assign = new Assign($responseVariable, $returnExpr);

        $if = new If_(new Instanceof_($responseVariable, new FullyQualified(self::RESPONSE_CLASS)));
        $if->stmts[] = new Return_($responseVariable);

        $thisRenderMethodCall->args[1] = new Arg($responseVariable);

        $returnThisRender = new Return_($thisRenderMethodCall);
        $this->addNodesAfterNode([$assign, $if, $returnThisRender], $return);
    }
}
