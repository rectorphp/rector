<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Renaming\ValueObject\MethodCallRename;

/**
 * @see \Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\RenameMethodRectorTest
 */
final class RenameMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OLD_TO_NEW_METHODS_BY_CLASS = 'old_to_new_methods_by_class';

    /**
     * @var MethodCallRename[]
     */
    private $methodCallRenames = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns method names to new ones.', [
            new ConfiguredCodeSample(
                <<<'PHP'
$someObject = new SomeExampleClass;
$someObject->oldMethod();
PHP
                ,
                <<<'PHP'
$someObject = new SomeExampleClass;
$someObject->newMethod();
PHP
                ,
                [
                    self::OLD_TO_NEW_METHODS_BY_CLASS => [
                        new MethodCallRename('SomeExampleClass', 'oldMethod', 'newMethod'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->methodCallRenames as $methodCallRename) {
            if (! $this->isMethodStaticCallOrClassMethodObjectType($node, $methodCallRename->getOldClass())) {
                continue;
            }

            if (! $this->isName($node->name, $methodCallRename->getOldMethod())) {
                continue;
            }

            if ($this->skipClassMethod($node, $methodCallRename)) {
                continue;
            }

            $node->name = new Identifier($methodCallRename->getNewMethod());

            return $node;
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->methodCallRenames = $configuration[self::OLD_TO_NEW_METHODS_BY_CLASS] ?? [];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    private function skipClassMethod(Node $node, MethodCallRename $methodCallRename): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        if ($this->shouldSkipForAlreadyExistingClassMethod($node, $methodCallRename)) {
            return true;
        }

        return $this->shouldSkipForExactClassMethodForClassMethod($node, $methodCallRename->getOldClass());
    }

    private function shouldSkipForAlreadyExistingClassMethod(
        ClassMethod $classMethod,
        MethodCallRename $methodCallRename
    ): bool {
        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        /** @var ClassLike|null $classLike */
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return false;
        }

        return (bool) $classLike->getMethod($methodCallRename->getNewMethod());
    }

    private function shouldSkipForExactClassMethodForClassMethod(ClassMethod $classMethod, string $type): bool
    {
        return $classMethod->getAttribute(AttributeKey::CLASS_NAME) === $type;
    }
}
