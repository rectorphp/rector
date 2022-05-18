<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\DataProvider\RouteMapProvider;
use Rector\Symfony\PhpDocNode\SymfonyRouteTagValueNodeFactory;
use Rector\Symfony\ValueObject\SymfonyRouteMetadata;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\AddRouteAnnotationRector\AddRouteAnnotationRectorTest
 */
class AddRouteAnnotationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\DataProvider\RouteMapProvider
     */
    private $routeMapProvider;
    /**
     * @readonly
     * @var \Rector\Symfony\PhpDocNode\SymfonyRouteTagValueNodeFactory
     */
    private $symfonyRouteTagValueNodeFactory;
    public function __construct(\Rector\Symfony\DataProvider\RouteMapProvider $routeMapProvider, \Rector\Symfony\PhpDocNode\SymfonyRouteTagValueNodeFactory $symfonyRouteTagValueNodeFactory)
    {
        $this->routeMapProvider = $routeMapProvider;
        $this->symfonyRouteTagValueNodeFactory = $symfonyRouteTagValueNodeFactory;
    }
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        $routeMap = $this->routeMapProvider->provide();
        if (!$routeMap->hasRoutes()) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $className = $this->nodeNameResolver->getName($class);
        $methodName = $this->nodeNameResolver->getName($node);
        $fqcnAndMethodName = \sprintf('%s::%s', $className, $methodName);
        $symfonyRouteMetadata = $routeMap->getRouteByMethod($fqcnAndMethodName);
        if (!$symfonyRouteMetadata instanceof \Rector\Symfony\ValueObject\SymfonyRouteMetadata) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Symfony\\Component\\Routing\\Annotation\\Route');
        if ($doctrineAnnotationTagValueNode !== null) {
            return null;
        }
        $items = ['path' => \sprintf('"%s"', $symfonyRouteMetadata->getPath()), 'name' => \sprintf('"%s"', $symfonyRouteMetadata->getName())];
        $defaults = $symfonyRouteMetadata->getDefaults();
        unset($defaults['_controller']);
        if ($defaults !== []) {
            $items['defaults'] = new \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode(\array_map(static function ($default) {
                switch (\true) {
                    case \is_string($default):
                        return \sprintf('"%s"', $default);
                    default:
                        return $default;
                }
            }, $defaults));
        }
        if ($symfonyRouteMetadata->getHost() !== '') {
            $items['host'] = \sprintf('"%s"', $symfonyRouteMetadata->getHost());
        }
        if ($symfonyRouteMetadata->getSchemes() !== []) {
            $items['schemes'] = new \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode(\array_map(static function (string $scheme) : string {
                return \sprintf('"%s"', $scheme);
            }, $symfonyRouteMetadata->getSchemes()));
        }
        if ($symfonyRouteMetadata->getMethods() !== []) {
            $items['methods'] = new \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode(\array_map(static function (string $scheme) : string {
                return \sprintf('"%s"', $scheme);
            }, $symfonyRouteMetadata->getMethods()));
        }
        if ($symfonyRouteMetadata->getCondition() !== '') {
            $items['condition'] = \sprintf('"%s"', $symfonyRouteMetadata->getCondition());
        }
        $phpDocInfo->addTagValueNode($this->symfonyRouteTagValueNodeFactory->createFromItems($items));
        return $node;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add route annotation to controller action', []);
    }
}
