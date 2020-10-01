<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\Use_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Restoration\NameMatcher\FullyQualifiedNameMatcher;

/**
 * @see \Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\RestoreFullyQualifiedNameRectorTest
 */
final class RestoreFullyQualifiedNameRector extends AbstractRector
{
    /**
     * @var FullyQualifiedNameMatcher
     */
    private $fullyQualifiedNameMatcher;

    public function __construct(FullyQualifiedNameMatcher $fullyQualifiedNameMatcher)
    {
        $this->fullyQualifiedNameMatcher = $fullyQualifiedNameMatcher;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Restore accidentally shortened class names to its fully qualified form.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use ShortClassOnly;

class AnotherClass
{
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
use App\Whatever\ShortClassOnly;

class AnotherClass
{
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
        return [Use_::class, Param::class, ClassMethod::class];
    }

    /**
     * @param Use_|Param|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Use_) {
            return $this->refactoryUse($node);
        }

        if ($node instanceof Param) {
            return $this->refactorParam($node);
        }

        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }

        return null;
    }

    private function refactoryUse(Use_ $use): Use_
    {
        foreach ($use->uses as $useUse) {
            $name = $useUse->name;

            $fullyQualifiedName = $this->fullyQualifiedNameMatcher->matchFullyQualifiedName($name);
            if (! $fullyQualifiedName instanceof FullyQualified) {
                continue;
            }

            $useUse->name = $fullyQualifiedName;
        }

        return $use;
    }

    private function refactorParam(Param $param): ?Param
    {
        $name = $param->type;
        if (! $name instanceof Name) {
            return null;
        }

        $fullyQualified = $this->fullyQualifiedNameMatcher->matchFullyQualifiedName($name);
        if ($fullyQualified === null) {
            return null;
        }

        $param->type = $fullyQualified;
        return $param;
    }

    private function refactorClassMethod(ClassMethod $classMethod): ?ClassMethod
    {
        $returnType = $classMethod->returnType;
        if ($returnType === null) {
            return null;
        }

        $fullyQualified = $this->fullyQualifiedNameMatcher->matchFullyQualifiedName($returnType);
        if ($fullyQualified === null) {
            return null;
        }

        $classMethod->returnType = $fullyQualified;

        return $classMethod;
    }
}
