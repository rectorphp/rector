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
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Renaming\Contract\MethodCallRenameInterface;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\RenameMethodRectorTest
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
    private $methodCallRenames = [];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
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
            if (! $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType(
                $node,
                $methodCallRename->getOldClass()
            )) {
                continue;
            }

            if (! $this->isName($node->name, $methodCallRename->getOldMethod())) {
                continue;
            }

            if ($this->skipClassMethod($node, $methodCallRename)) {
                continue;
            }

            if ($this->skipMethodCall($node, $methodCallRename)) {
                continue;
            }

            if ($this->skipStaticCall($node, $methodCallRename)) {
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

    public function configure(array $configuration): void
    {
        $methodCallRenames = $configuration[self::METHOD_CALL_RENAMES] ?? [];
        Assert::allIsInstanceOf($methodCallRenames, MethodCallRenameInterface::class);

        $this->methodCallRenames = $methodCallRenames;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    private function skipClassMethod(Node $node, MethodCallRenameInterface $methodCallRename): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        if ($this->shouldSkipForAlreadyExistingClassMethod($node, $methodCallRename)) {
            return true;
        }

        if ($this->shouldSkipWhenClassMethodIsPartOfInterface($node, $methodCallRename)) {
            return true;
        }

        return $this->shouldSkipForExactClassMethodForClassMethodOrTargetInvokePrivate(
            $node,
            $methodCallRename->getOldClass(),
            $methodCallRename->getNewMethod()
        );
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    private function skipMethodCall(Node $node, MethodCallRenameInterface $methodCallRename): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        $type = $this->nodeTypeResolver->resolve($node->var);
        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (! $unionedType instanceof ObjectType) {
                    continue;
                }

                return $this->isInterfaceOrMethodExistsInParentOrInterface(
                    $unionedType->getClassName(),
                    $methodCallRename->getOldMethod()
                );
            }
        }

        if ($type instanceof ObjectType) {
            return $this->isInterfaceOrMethodExistsInParentOrInterface(
                $type->getClassName(),
                $methodCallRename->getOldMethod()
            );
        }

        return false;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    private function skipStaticCall(Node $node, MethodCallRenameInterface $methodCallRename): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        return $this->isInterfaceOrMethodExistsInParentOrInterface(
            $node->class->toString(),
            $methodCallRename->getOldMethod()
        );
    }

    private function isInterfaceOrMethodExistsInParentOrInterface(string $class, string $method) : bool
    {
        if (interface_exists($class)) {
            return true;
        }

        return $this->classManipulator->hasParentMethodOrInterface($class, $method);
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

    private function shouldSkipWhenClassMethodIsPartOfInterface(
        ClassMethod $classMethod,
        MethodCallRenameInterface $methodCallRename
    ): bool {
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return false;
        }

        $className = $classLike->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        return $this->classManipulator->hasParentMethodOrInterface($className, $methodCallRename->getOldMethod());
    }

    private function shouldSkipForExactClassMethodForClassMethodOrTargetInvokePrivate(
        ClassMethod $classMethod,
        string $type,
        string $newMethodName
    ): bool {
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        $methodCalls = $this->nodeRepository->findMethodCallsOnClass($className);

        $name = $this->getName($classMethod->name);
        if (isset($methodCalls[$name])) {
            return false;
        }

        $isExactClassMethodForClasssMethod = $classMethod->getAttribute(AttributeKey::CLASS_NAME) === $type;
        if ($isExactClassMethodForClasssMethod) {
            return true;
        }

        if ($classMethod->isPublic()) {
            return false;
        }

        $newClassMethod = clone $classMethod;
        $newClassMethod->name = new Identifier($newMethodName);
        return $newClassMethod->isMagic();
    }
}
