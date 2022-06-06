<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\General;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\NodeManipulator\ClassDependencyManipulator;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\PostRector\ValueObject\PropertyMetadata;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/DependencyInjection/Index.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\General\InjectMethodToConstructorInjectionRector\InjectMethodToConstructorInjectionRectorTest
 */
final class InjectMethodToConstructorInjectionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    public function __construct(ClassDependencyManipulator $classDependencyManipulator)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
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
            if (!$param->type instanceof FullyQualified) {
                continue;
            }
            $paramName = $this->getName($param->var);
            if (null === $paramName) {
                continue;
            }
            $this->classDependencyManipulator->addConstructorDependency($node, new PropertyMetadata($paramName, new ObjectType((string) $param->type), Class_::MODIFIER_PROTECTED));
            $this->nodeRemover->removeNodeFromStatements($node, $injectMethod);
        }
        return $node;
    }
    private function shouldSkip(Class_ $class) : bool
    {
        return [] === $class->getMethods();
    }
}
