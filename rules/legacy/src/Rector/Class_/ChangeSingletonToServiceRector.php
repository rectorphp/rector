<?php

declare(strict_types=1);

namespace Rector\Legacy\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;
use Rector\Legacy\NodeAnalyzer\SingletonClassMethodAnalyzer;

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
        Class_ $class,
        string $getSingletonMethodName,
        string $singletonPropertyName
    ): Class_ {
        foreach ($class->getMethods() as $property) {
            if ($this->isName($property, $getSingletonMethodName)) {
                $this->removeNodeFromStatements($class, $property);
                continue;
            }

            if (! $this->isNames($property, [MethodName::CONSTRUCT, '__clone', '__wakeup'])) {
                continue;
            }

            if ($property->isPublic()) {
                continue;
            }

            // remove non-public empty
            if ($property->stmts === []) {
                $this->removeNodeFromStatements($class, $property);
            } else {
                $this->makePublic($property);
            }
        }

        $this->removePropertyByName($class, $singletonPropertyName);

        return $class;
    }

    private function removePropertyByName(Class_ $class, string $propertyName): void
    {
        foreach ($class->getProperties() as $property) {
            if (! $this->isName($property, $propertyName)) {
                continue;
            }

            $this->removeNodeFromStatements($class, $property);
        }
    }
}
