<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\General;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/DependencyInjection/Index.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\General\InjectMethodToConstructorInjectionRector\InjectMethodToConstructorInjectionRectorTest
 */
final class InjectMethodToConstructorInjectionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    public function __construct(\Rector\Core\NodeManipulator\ClassDependencyManipulator $classDependencyManipulator)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
namespace App\Service;
use \TYPO3\CMS\Core\Cache\CacheManager;
class Service
{
    private CacheManager $cacheManager;
    public function injectCacheManager(CacheManager $cacheManager): void
    {
        $this->cacheManager = $cacheManager;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
namespace App\Service;
use \TYPO3\CMS\Core\Cache\CacheManager;
class Service
{
    private CacheManager $cacheManager;
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * List of nodes this class checks, classes that implements \PhpParser\Node See beautiful map of all nodes
     * https://github.com/rectorphp/rector/blob/master/docs/NodesOverview.md
     *
     * @return class-string[]
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * Process Node of matched type
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        /** @var Class_ $node */
        if ($this->shouldSkip($node)) {
            return null;
        }
        $injectMethods = \array_filter($node->getMethods(), function ($classMethod) {
            return \strncmp((string) $classMethod->name, 'inject', \strlen('inject')) === 0;
        });
        if ([] === $injectMethods) {
            return null;
        }
        foreach ($injectMethods as $injectMethod) {
            $params = $injectMethod->getParams();
            if ([] === $params) {
                continue;
            }
            \reset($params);
            /** @var Param $param */
            $param = \current($params);
            if (!$param->type instanceof \PhpParser\Node\Name\FullyQualified) {
                continue;
            }
            $paramName = $this->getName($param->var);
            if (null === $paramName) {
                continue;
            }
            $this->classDependencyManipulator->addConstructorDependency($node, new \Rector\PostRector\ValueObject\PropertyMetadata($paramName, new \PHPStan\Type\ObjectType((string) $param->type), \PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED));
            $this->nodeRemover->removeNodeFromStatements($node, $injectMethod);
        }
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        return [] === $class->getMethods();
    }
}
