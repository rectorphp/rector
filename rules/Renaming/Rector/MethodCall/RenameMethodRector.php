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
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Renaming\Collector\MethodCallRenameCollector;
use Rector\Renaming\Contract\MethodCallRenameInterface;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\RenameMethodRectorTest
 */
final class RenameMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_CALL_RENAMES = 'method_call_renames';

    /**
     * @var MethodCallRenameInterface[]
     */
    private array $methodCallRenames = [];

    public function __construct(
        private ClassManipulator $classManipulator,
        private MethodCallRenameCollector $methodCallRenameCollector
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns method names to new ones.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->oldMethod();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->newMethod();
CODE_SAMPLE
                ,
                [
                    self::METHOD_CALL_RENAMES => [
                        new MethodCallRename('SomeExampleClass', 'oldMethod', 'newMethod'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
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
            $implementsInterface = $this->classManipulator->hasParentMethodOrInterface(
                $methodCallRename->getOldObjectType(),
                $methodCallRename->getOldMethod()
            );
            if ($implementsInterface) {
                continue;
            }

            if (! $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType(
                $node,
                $methodCallRename->getOldObjectType()
            )) {
                continue;
            }

            if (! $this->isName($node->name, $methodCallRename->getOldMethod())) {
                continue;
            }

            if ($this->shouldSkipClassMethod($node, $methodCallRename)) {
                continue;
            }

            $node->name = new Identifier($methodCallRename->getNewMethod());

            if ($methodCallRename instanceof MethodCallRenameWithArrayKey && ! $node instanceof ClassMethod) {
                return new ArrayDimFetch($node, BuilderHelpers::normalizeValue($methodCallRename->getArrayKey()));
            }

            return $node;
        }

        return null;
    }

    /**
     * @param array<string, MethodCallRenameInterface[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $methodCallRenames = $configuration[self::METHOD_CALL_RENAMES] ?? [];
        Assert::allIsInstanceOf($methodCallRenames, MethodCallRenameInterface::class);

        $this->methodCallRenames = $methodCallRenames;
        $this->methodCallRenameCollector->addMethodCallRenames($methodCallRenames);
    }

    private function shouldSkipClassMethod(
        MethodCall | StaticCall | ClassMethod $node,
        MethodCallRenameInterface $methodCallRename
    ): bool {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        return $this->shouldSkipForAlreadyExistingClassMethod($node, $methodCallRename);
    }

    private function shouldSkipForAlreadyExistingClassMethod(
        ClassMethod $classMethod,
        MethodCallRenameInterface $methodCallRename
    ): bool {
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return false;
        }

        return (bool) $classLike->getMethod($methodCallRename->getNewMethod());
    }
}
