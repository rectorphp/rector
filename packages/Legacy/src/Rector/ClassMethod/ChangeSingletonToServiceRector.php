<?php declare(strict_types=1);

namespace Rector\Legacy\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Legacy\NodeAnalyzer\SingletonClassMethodAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/lifbH
 * @see https://stackoverflow.com/a/203359/1348344
 * @see http://cleancode.blog/2017/07/20/how-to-avoid-many-instances-in-singleton-pattern/
 * @see \Rector\Legacy\Tests\Rector\ClassMethod\ChangeSingletonToServiceRector\ChangeSingletonToServiceRectorTest
 */
final class ChangeSingletonToServiceRector extends AbstractRector
{
    /**
     * @var SingletonClassMethodAnalyzer
     */
    private $singletonClassMethodAnalyzer;

    public function __construct(SingletonClassMethodAnalyzer $singletonClassMethodAnalyzer)
    {
        $this->singletonClassMethodAnalyzer = $singletonClassMethodAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change singleton class to normal class that can be registered as a service', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function __construct()
    {
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isAnonymous()) {
            return null;
        }

        $match = $this->matchStaticPropertyFetchAndGetSingletonMethodName($node);
        if ($match === null) {
            return null;
        }

        [$singletonPropertyName, $getSingletonMethodName] = $match;

        return $this->refactorClassStmts($node, $getSingletonMethodName, $singletonPropertyName);
    }

    /**
     * @param Class_ $class
     * @return string[]|null
     */
    private function matchStaticPropertyFetchAndGetSingletonMethodName(Class_ $class): ?array
    {
        foreach ($class->getMethods() as $classMethod) {
            if (! $classMethod->isStatic()) {
                continue;
            }

            $staticPropertyFetch = $this->singletonClassMethodAnalyzer->matchStaticPropertyFetch($classMethod);
            if ($staticPropertyFetch === null) {
                return null;
            }

            return [$this->getName($staticPropertyFetch), $this->getName($classMethod)];
        }

        return null;
    }

    private function refactorClassStmts(
        Class_ $node,
        string $getSingletonMethodName,
        string $singletonPropertyName
    ): Class_ {
        foreach ($node->getMethods() as $property) {
            if ($this->isName($property, $getSingletonMethodName)) {
                $this->removeNodeFromStatements($node, $property);
                continue;
            }

            if (! $this->isNames($property, ['__construct', '__clone', '__wakeup'])) {
                continue;
            }

            if (! $property->isPublic()) {
                // remove non-public empty
                if ($property->stmts === []) {
                    $this->removeNodeFromStatements($node, $property);
                } else {
                    $this->makePublic($property);
                }
            }
        }

        foreach ($node->getProperties() as $property) {
            if (! $this->isName($property, $singletonPropertyName)) {
                continue;
            }

            $this->removeNodeFromStatements($node, $property);
        }

        return $node;
    }
}
