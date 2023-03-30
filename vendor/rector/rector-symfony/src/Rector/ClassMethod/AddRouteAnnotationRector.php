<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\Symfony\PhpDocNode\SymfonyRouteTagValueNodeFactory;
use Rector\Symfony\ValueObject\SymfonyRouteMetadata;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\AddRouteAnnotationRector\AddRouteAnnotationRectorTest
 */
final class AddRouteAnnotationRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface
     */
    private $symfonyRoutesProvider;
    /**
     * @readonly
     * @var \Rector\Symfony\PhpDocNode\SymfonyRouteTagValueNodeFactory
     */
    private $symfonyRouteTagValueNodeFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser
     */
    private $arrayParser;
    public function __construct(SymfonyRoutesProviderInterface $symfonyRoutesProvider, SymfonyRouteTagValueNodeFactory $symfonyRouteTagValueNodeFactory, ArrayParser $arrayParser)
    {
        $this->symfonyRoutesProvider = $symfonyRoutesProvider;
        $this->symfonyRouteTagValueNodeFactory = $symfonyRouteTagValueNodeFactory;
        $this->arrayParser = $arrayParser;
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        // only public methods can be controller routes
        if (!$node->isPublic()) {
            return null;
        }
        if ($node->isStatic()) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        if ($this->symfonyRoutesProvider->provide() === []) {
            return null;
        }
        $controllerReference = $this->resolveControllerReference($class, $node);
        if (!$controllerReference) {
            return null;
        }
        // is there a route for this annotation?
        $symfonyRouteMetadatas = $this->matchSymfonyRouteMetadataByControllerReference($controllerReference);
        if ($symfonyRouteMetadatas === []) {
            return null;
        }
        // skip if already has an annotation
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass(SymfonyAnnotation::ROUTE);
        if ($doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        foreach ($symfonyRouteMetadatas as $symfonyRouteMetadata) {
            $items = $this->createRouteItems($symfonyRouteMetadata);
            $symfonyRouteTagValueNode = $this->symfonyRouteTagValueNodeFactory->createFromItems($items);
            $phpDocInfo->addTagValueNode($symfonyRouteTagValueNode);
        }
        return $node;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Collect routes from Symfony project router and add Route annotation to controller action', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SomeController extends AbstractController
{
    public function index()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends AbstractController
{
    /**
     * @Route(name="homepage", path="/welcome")
     */
    public function index()
    {
    }
}
CODE_SAMPLE
)]);
    }
    private function resolveControllerReference(Class_ $class, ClassMethod $classMethod) : ?string
    {
        $className = $this->nodeNameResolver->getName($class);
        $methodName = $this->nodeNameResolver->getName($classMethod);
        if ($methodName === '__invoke') {
            return $className;
        }
        return $className . '::' . $methodName;
    }
    /**
     * @return ArrayItemNode[]
     */
    private function createRouteItems(SymfonyRouteMetadata $symfonyRouteMetadata) : array
    {
        $arrayItemNodes = [];
        $arrayItemNodes[] = new ArrayItemNode($symfonyRouteMetadata->getPath(), 'path', String_::KIND_DOUBLE_QUOTED);
        $arrayItemNodes[] = new ArrayItemNode($symfonyRouteMetadata->getName(), 'name', String_::KIND_DOUBLE_QUOTED);
        if ($symfonyRouteMetadata->getRequirements() !== []) {
            $requriementsCurlyList = $this->createCurlyQuoted($symfonyRouteMetadata->getRequirements());
            $arrayItemNodes[] = new ArrayItemNode($requriementsCurlyList, 'requirements');
        }
        $optionsWithoutDefaultCompilerClass = $symfonyRouteMetadata->getOptionsWithoutDefaultCompilerClass();
        if ($optionsWithoutDefaultCompilerClass !== []) {
            $optionsCurlyQuoted = $this->createCurlyQuoted($optionsWithoutDefaultCompilerClass);
            $arrayItemNodes[] = new ArrayItemNode($optionsCurlyQuoted, 'options');
        }
        $defaultsWithoutController = $symfonyRouteMetadata->getDefaultsWithoutController();
        if ($defaultsWithoutController !== []) {
            $defaultsWithoutControllerCurlyList = $this->createCurlyQuoted($defaultsWithoutController);
            $arrayItemNodes[] = new ArrayItemNode($defaultsWithoutControllerCurlyList, 'defaults');
        }
        if ($symfonyRouteMetadata->getHost() !== '') {
            $arrayItemNodes[] = new ArrayItemNode($symfonyRouteMetadata->getHost(), 'host', String_::KIND_DOUBLE_QUOTED);
        }
        if ($symfonyRouteMetadata->getMethods() !== []) {
            $methodsCurlyList = $this->createCurlyQuoted($symfonyRouteMetadata->getMethods());
            $arrayItemNodes[] = new ArrayItemNode($methodsCurlyList, 'methods');
        }
        if ($symfonyRouteMetadata->getSchemes() !== []) {
            $schemesArrayItemNodes = $this->createCurlyQuoted($symfonyRouteMetadata->getSchemes());
            $arrayItemNodes[] = new ArrayItemNode($schemesArrayItemNodes, 'schemes');
        }
        if ($symfonyRouteMetadata->getCondition() !== '') {
            $arrayItemNodes[] = new ArrayItemNode($symfonyRouteMetadata->getCondition(), 'condition', String_::KIND_DOUBLE_QUOTED);
        }
        return $arrayItemNodes;
    }
    /**
     * @return SymfonyRouteMetadata[]
     */
    private function matchSymfonyRouteMetadataByControllerReference(string $controllerReference) : array
    {
        $matches = [];
        foreach ($this->symfonyRoutesProvider->provide() as $symfonyRouteMetadata) {
            if ($symfonyRouteMetadata->getControllerReference() === $controllerReference) {
                $matches[] = $symfonyRouteMetadata;
            }
        }
        return $matches;
    }
    /**
     * @param mixed[] $values
     */
    private function createCurlyQuoted(array $values) : CurlyListNode
    {
        $methodsArrayItems = $this->arrayParser->createArrayFromValues($values);
        $curlyListNode = new CurlyListNode($methodsArrayItems);
        foreach ($curlyListNode->values as $nestedMethodsArrayItem) {
            if (\is_string($nestedMethodsArrayItem->value)) {
                $nestedMethodsArrayItem->kindValueQuoted = String_::KIND_DOUBLE_QUOTED;
            }
            if (\is_string($nestedMethodsArrayItem->key)) {
                $nestedMethodsArrayItem->kindKeyQuoted = String_::KIND_DOUBLE_QUOTED;
            }
            if ($nestedMethodsArrayItem->value === null) {
                $nestedMethodsArrayItem->value = 'null';
            } elseif (\is_bool($nestedMethodsArrayItem->value)) {
                $nestedMethodsArrayItem->value = $nestedMethodsArrayItem->value ? 'true' : 'false';
            }
        }
        return $curlyListNode;
    }
}
