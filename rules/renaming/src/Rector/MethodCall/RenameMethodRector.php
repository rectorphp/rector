<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\MethodCall;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
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

/**
 * @see \Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\RenameMethodRectorTest
 */
final class RenameMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OLD_TO_NEW_METHODS_BY_CLASS = '$oldToNewMethodsByClass';

    /**
     * class => [
     *     oldMethod => newMethod
     * ]
     *
     * @var array<string, array<string, string>>
     */
    private $oldToNewMethodsByClass = [];

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
                        'SomeExampleClass' => [
                            '$oldToNewMethodsByClass' => [
                                'oldMethod' => 'newMethod',
                            ],
                        ],
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
        foreach ($this->oldToNewMethodsByClass as $type => $oldToNewMethods) {
            if (! $this->isMethodStaticCallOrClassMethodObjectType($node, $type)) {
                continue;
            }

            foreach ($oldToNewMethods as $oldMethod => $newMethod) {
                if (! $this->isName($node->name, $oldMethod)) {
                    continue;
                }

                if ($this->skipClassMethod($node, $newMethod, $type)) {
                    continue;
                }

                $newNode = $this->renameToMethod($node, $newMethod);
                if ($newNode !== null) {
                    return $newNode;
                }
            }
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->oldToNewMethodsByClass = $configuration[self::OLD_TO_NEW_METHODS_BY_CLASS] ?? [];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @param string|mixed[] $newMethod
     */
    private function renameToMethod(Node $node, $newMethod): ?Node
    {
        if (is_string($newMethod)) {
            $node->name = new Identifier($newMethod);

            return $node;
        }

        // special case for array dim fetch
        if (! $node instanceof ClassMethod) {
            $node->name = new Identifier($newMethod['name']);

            return new ArrayDimFetch($node, BuilderHelpers::normalizeValue($newMethod['array_key']));
        }

        return null;
    }

    /**
     * @param string|mixed[] $newMethod
     */
    private function shouldSkipForAlreadyExistingClassMethod(ClassMethod $classMethod, $newMethod): bool
    {
        if (! is_string($newMethod)) {
            return false;
        }

        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        /** @var ClassLike|null $classLike */
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return false;
        }

        return (bool) $classLike->getMethod($newMethod);
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @param string|string[] $newMethod
     */
    private function skipClassMethod(Node $node, $newMethod, string $type): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        if ($this->shouldSkipForAlreadyExistingClassMethod($node, $newMethod)) {
            return true;
        }

        return $this->shouldSkipForExactClassMethodForClassMethod($node, $type);
    }

    private function shouldSkipForExactClassMethodForClassMethod(ClassMethod $classMethod, string $type): bool
    {
        return $classMethod->getAttribute(AttributeKey::CLASS_NAME) === $type;
    }
}
