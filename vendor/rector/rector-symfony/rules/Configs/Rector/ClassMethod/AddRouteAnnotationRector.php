<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
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
 * @see \Rector\Symfony\Tests\Configs\Rector\ClassMethod\AddRouteAnnotationRector\AddRouteAnnotationRectorTest
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->symfonyRoutesProvider->provide() === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            // only public methods can be controller routes
            if (!$classMethod->isPublic()) {
                continue;
            }
            if ($classMethod->isStatic()) {
                continue;
            }
            $controllerReference = $this->resolveControllerReference($node, $classMethod);
            if (!$controllerReference) {
                continue;
            }
            // is there a route for this annotation?
            $symfonyRouteMetadatas = $this->matchSymfonyRouteMetadataByControllerReference($controllerReference);
            if ($symfonyRouteMetadatas === []) {
                continue;
            }
            // skip if already has an annotation
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass(SymfonyAnnotation::ROUTE);
            if ($doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            foreach ($symfonyRouteMetadatas as $symfonyRouteMetadata) {
                $items = $this->createRouteItems($symfonyRouteMetadata);
                $symfonyRouteTagValueNode = $this->symfonyRouteTagValueNodeFactory->createFromItems($items);
                $phpDocInfo->addTagValueNode($symfonyRouteTagValueNode);
            }
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
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
        $arrayItemNodes[] = new ArrayItemNode(new StringNode($symfonyRouteMetadata->getPath()), 'path');
        $arrayItemNodes[] = new ArrayItemNode(new StringNode($symfonyRouteMetadata->getName()), 'name');
        if ($symfonyRouteMetadata->getRequirements() !== []) {
            $curlyListNode = $this->createCurlyQuoted($symfonyRouteMetadata->getRequirements());
            $arrayItemNodes[] = new ArrayItemNode($curlyListNode, 'requirements');
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
            $arrayItemNodes[] = new ArrayItemNode(new StringNode($symfonyRouteMetadata->getHost()), 'host');
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
            $arrayItemNodes[] = new ArrayItemNode(new StringNode($symfonyRouteMetadata->getCondition()), 'condition');
        }
        return $arrayItemNodes;
    }
    /**
     * @return SymfonyRouteMetadata[]
     */
    private function matchSymfonyRouteMetadataByControllerReference(string $controllerReference) : array
    {
        $matches = [];
        foreach ($this->symfonyRoutesProvider->provide() as $symfonyRouteMetadatum) {
            if ($symfonyRouteMetadatum->getControllerReference() === $controllerReference) {
                $matches[] = $symfonyRouteMetadatum;
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
                $nestedMethodsArrayItem->value = new StringNode($nestedMethodsArrayItem->value);
            }
            if (\is_string($nestedMethodsArrayItem->key)) {
                $nestedMethodsArrayItem->key = new StringNode($nestedMethodsArrayItem->key);
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
