<?php

declare(strict_types=1);

namespace Rector\Sensio\Rector\FrameworkExtraBundle;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\Core\PhpParser\Node\Manipulator\FuncCallManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Sensio\Helper\TemplateGuesser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see https://github.com/symfony/symfony-docs/pull/12387#discussion_r329551967
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/view.html
 * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/issues/641
 *
 * @see \Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\TemplateAnnotationVersion3RectorTest
 * @see \Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\TemplateAnnotationVersion5RectorTest
 */
final class TemplateAnnotationRector extends AbstractRector
{
    /**
     * @var int
     */
    private $version;

    /**
     * @var TemplateGuesser
     */
    private $templateGuesser;

    /**
     * @var FuncCallManipulator
     */
    private $funcCallManipulator;

    public function __construct(
        TemplateGuesser $templateGuesser,
        FuncCallManipulator $funcCallManipulator,
        int $version = 3
    ) {
        $this->templateGuesser = $templateGuesser;
        $this->version = $version;
        $this->funcCallManipulator = $funcCallManipulator;
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

        $node->extends = new FullyQualified(AbstractController::class);

        return $node;
    }

    private function replaceTemplateAnnotation(ClassMethod $classMethod): ?Node
    {
        if (! $classMethod->isPublic()) {
            return null;
        }

        /** @var SensioTemplateTagValueNode|null $sensioTemplateTagValueNode */
        $sensioTemplateTagValueNode = $this->getSensioTemplateTagValueNode($classMethod);
        if ($sensioTemplateTagValueNode === null) {
            return null;
        }

        $this->updateReturnType($classMethod);
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

    private function getSensioTemplateTagValueNode(ClassMethod $classMethod): ?SensioTemplateTagValueNode
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getByType(SensioTemplateTagValueNode::class);
    }

    private function updateReturnType(ClassMethod $classMethod): void
    {
        $this->updateReturnPhpDoc($classMethod);

        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return;
        }

        // change return type
        if ($classMethod->returnType !== null) {
            $returnTypeName = $this->getName($classMethod->returnType);
            if ($returnTypeName !== null && is_a($returnTypeName, Response::class, true)) {
                return;
            }
        }

        $classMethod->returnType = new FullyQualified(Response::class);
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
        $thisRenderMethodCall = $this->createThisRender($classMethod, $returnNode, $sensioTemplateTagValueNode);

        if ($returnNode === null) {
            // or add as last statement in the method
            $classMethod->stmts[] = new Return_($thisRenderMethodCall);
        } elseif ($returnNode->expr !== null) {
            if ($this->isFuncCallName($returnNode->expr, 'compact')) {
                /** @var FuncCall $compactFunCall */
                $compactFunCall = $returnNode->expr;

                $array = $this->createArrayFromCompactFuncCall($compactFunCall);
                $thisRenderMethodCall->args[1] = new Arg($array);
                $returnNode->expr = $thisRenderMethodCall;
            } elseif (! $returnNode->expr instanceof MethodCall) {
                $returnNode->expr = $thisRenderMethodCall;
            }
        }

        // replace Return_ node value if exists and is not already in correct format

        $this->updateReturnType($classMethod);
    }

    private function createThisRender(
        ClassMethod $classMethod,
        ?Return_ $return,
        SensioTemplateTagValueNode $sensioTemplateTagValueNode
    ): MethodCall {
        $renderArguments = $this->resolveRenderArguments($classMethod, $return, $sensioTemplateTagValueNode);

        return $this->createMethodCall('this', 'render', $renderArguments);
    }

    /**
     * @return Arg[]
     */
    private function resolveRenderArguments(
        ClassMethod $classMethod,
        ?Return_ $return,
        SensioTemplateTagValueNode $sensioTemplateTagValueNode
    ): array {
        $arguments = [$this->resolveTemplateName($classMethod, $sensioTemplateTagValueNode)];

        if ($return === null) {
            return $this->createArgs($arguments);
        }

        if ($return->expr instanceof Array_ && count($return->expr->items)) {
            $arguments[] = $return->expr;
        }

        $arguments = array_merge($arguments, $this->resolveArrayArgumentsFromMethodCall($return));

        return $this->createArgs($arguments);
    }

    private function resolveTemplateName(
        ClassMethod $classMethod,
        SensioTemplateTagValueNode $sensioTemplateTagValueNode
    ): string {
        if ($sensioTemplateTagValueNode->getTemplate() !== null) {
            return $sensioTemplateTagValueNode->getTemplate();
        }

        return $this->templateGuesser->resolveFromClassMethodNode($classMethod, $this->version);
    }

    /**
     * Already existing method call
     *
     * @return Array_[]
     */
    private function resolveArrayArgumentsFromMethodCall(Return_ $returnNode): array
    {
        if (! $returnNode->expr instanceof MethodCall) {
            return [];
        }

        $arguments = [];
        foreach ($returnNode->expr->args as $arg) {
            if (! $arg->value instanceof Array_) {
                continue;
            }

            $arguments[] = $arg->value;
        }

        return $arguments;
    }

    private function createArrayFromCompactFuncCall(FuncCall $compactFuncCall): Array_
    {
        $compactVariableNames = $this->funcCallManipulator->extractArgumentsFromCompactFuncCalls([$compactFuncCall]);

        $array = new Array_();
        foreach ($compactVariableNames as $compactVariableName) {
            $arrayItem = new ArrayItem(new Variable($compactVariableName), new String_($compactVariableName));
            $array->items[] = $arrayItem;
        }
        return $array;
    }

    private function updateReturnPhpDoc(ClassMethod $classMethod): void
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $returnTagValueNode = $phpDocInfo->getByType(ReturnTagValueNode::class);
        if ($returnTagValueNode === null) {
            return;
        }

        $returnStaticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
            $returnTagValueNode->type,
            $classMethod
        );

        if ($returnStaticType instanceof ArrayType || $returnStaticType instanceof UnionType) {
            $returnTagValueNode->type = new IdentifierTypeNode('\Symfony\Component\HttpFoundation\Response');
        }
    }
}
