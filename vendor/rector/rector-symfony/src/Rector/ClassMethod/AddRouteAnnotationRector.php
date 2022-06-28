<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\Symfony\NodeFactory\Annotations\ValueQuoteWrapper;
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
     * @var \Rector\Symfony\NodeFactory\Annotations\ValueQuoteWrapper
     */
    private $valueQuoteWrapper;
    public function __construct(SymfonyRoutesProviderInterface $symfonyRoutesProvider, SymfonyRouteTagValueNodeFactory $symfonyRouteTagValueNodeFactory, ValueQuoteWrapper $valueQuoteWrapper)
    {
        $this->symfonyRoutesProvider = $symfonyRoutesProvider;
        $this->symfonyRouteTagValueNodeFactory = $symfonyRouteTagValueNodeFactory;
        $this->valueQuoteWrapper = $valueQuoteWrapper;
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
        // is there a route for this annotation?
        $symfonyRouteMetadata = $this->matchSymfonyRouteMetadataByControllerReference($controllerReference);
        if (!$symfonyRouteMetadata instanceof SymfonyRouteMetadata) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass(SymfonyAnnotation::ROUTE);
        if ($doctrineAnnotationTagValueNode !== null) {
            return null;
        }
        $items = $this->createRouteItems($symfonyRouteMetadata);
        $symfonyRouteTagValueNode = $this->symfonyRouteTagValueNodeFactory->createFromItems($items);
        $phpDocInfo->addTagValueNode($symfonyRouteTagValueNode);
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
    private function resolveControllerReference(Class_ $class, ClassMethod $classMethod) : string
    {
        $className = $this->nodeNameResolver->getName($class);
        $methodName = $this->nodeNameResolver->getName($classMethod);
        return $className . '::' . $methodName;
    }
    /**
     * @return array{path: string, name: string, defaults?: CurlyListNode, host?: string, methods?: CurlyListNode, condition?: string, options?: CurlyListNode}
     */
    private function createRouteItems(SymfonyRouteMetadata $symfonyRouteMetadata) : array
    {
        $items = ['path' => $this->valueQuoteWrapper->wrap($symfonyRouteMetadata->getPath()), 'name' => $this->valueQuoteWrapper->wrap($symfonyRouteMetadata->getName())];
        $defaultsWithoutController = $symfonyRouteMetadata->getDefaultsWithoutController();
        if ($defaultsWithoutController !== []) {
            $items['defaults'] = $this->valueQuoteWrapper->wrap($defaultsWithoutController);
        }
        if ($symfonyRouteMetadata->getHost() !== '') {
            $items['host'] = $this->valueQuoteWrapper->wrap($symfonyRouteMetadata->getHost());
        }
        if ($symfonyRouteMetadata->getSchemes() !== []) {
            $items['schemes'] = $this->valueQuoteWrapper->wrap($symfonyRouteMetadata->getSchemes());
        }
        if ($symfonyRouteMetadata->getMethods() !== []) {
            $items['methods'] = $this->valueQuoteWrapper->wrap($symfonyRouteMetadata->getMethods());
        }
        if ($symfonyRouteMetadata->getCondition() !== '') {
            $items['condition'] = $this->valueQuoteWrapper->wrap($symfonyRouteMetadata->getCondition());
        }
        if ($symfonyRouteMetadata->getRequirements() !== []) {
            $items['requirements'] = $this->valueQuoteWrapper->wrap($symfonyRouteMetadata->getRequirements());
        }
        $optionsWithoutDefaultCompilerClass = $symfonyRouteMetadata->getOptionsWithoutDefaultCompilerClass();
        if ($optionsWithoutDefaultCompilerClass !== []) {
            $items['options'] = $this->valueQuoteWrapper->wrap($optionsWithoutDefaultCompilerClass);
        }
        return $items;
    }
    private function matchSymfonyRouteMetadataByControllerReference(string $controllerReference) : ?SymfonyRouteMetadata
    {
        foreach ($this->symfonyRoutesProvider->provide() as $symfonyRouteMetadata) {
            if ($symfonyRouteMetadata->getControllerReference() === $controllerReference) {
                return $symfonyRouteMetadata;
            }
        }
        return null;
    }
}
