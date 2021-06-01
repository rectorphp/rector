<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Parser;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\DowngradeNamedArgumentRectorTest
 */
final class DowngradeNamedArgumentRector extends AbstractRector
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private SmartFileSystem $smartFileSystem,
        private Parser $parser
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove named argument',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    private function execute(?array $a = null, ?array $b = null)
    {
    }

    public function run(string $name = null, array $attributes = [])
    {
        $this->execute(a: [[$name ?? 0 => $attributes]]);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    private function execute(?array $a = null, ?array $b = null)
    {
    }

    public function run(string $name = null, array $attributes = [])
    {
        $this->execute([[$name ?? 0 => $attributes]]);
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $args = $node->args;
        if ($this->shouldSkip($args)) {
            return null;
        }

        $this->applyRemoveNamedArgument($node, $args);
        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     * @param Arg[] $args
     */
    private function applyRemoveNamedArgument(Node $node, array $args): ?Node
    {
        $caller = $this->getCaller($node);
        if (! $caller instanceof ClassMethod) {
            return null;
        }

        return $this->processRemoveNamedArgument($caller, $node, $args);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function getCaller(Node $node): ?Node
    {
        $caller = $node instanceof StaticCall
            ? $this->nodeRepository->findClassMethodByStaticCall($node)
            : $this->nodeRepository->findClassMethodByMethodCall($node);

        if ($caller instanceof ClassMethod) {
            return $caller;
        }

        $type = $node instanceof StaticCall
            ? $this->nodeTypeResolver->resolve($node->class)
            : $this->nodeTypeResolver->resolve($node->var);

        if (! $type instanceof ObjectType) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($type->getClassName());
        $fileName = $classReflection->getFileName();
        if (! is_string($fileName)) {
            return null;
        }

        $stmts = $this->parser->parse($this->smartFileSystem->readFile($fileName));
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findInstanceOf((array) $stmts, ClassLike::class);

        foreach ($classLikes as $classLike) {
            $caller = $classLike->getMethod((string) $this->getName($node->name));
            if (! $caller instanceof ClassMethod) {
                continue;
            }

            return $caller;
        }

        return null;
    }

    /**
     * @param MethodCall|StaticCall $node
     * @param Arg[] $args
     */
    private function processRemoveNamedArgument(ClassMethod $classMethod, Node $node, array $args): Expr
    {
        $params = $classMethod->params;
        /** @var Arg[] $newArgs */
        $newArgs = [];
        $keyParam = 0;

        foreach ($params as $keyParam => $param) {
            /** @var string $paramName */
            $paramName = $this->getName($param);

            foreach ($args as $arg) {
                /** @var string|null $argName */
                $argName = $this->getName($arg);

                if ($paramName === $argName) {
                    $newArgs[$keyParam] = new Arg(
                        $arg->value,
                        $arg->byRef,
                        $arg->unpack,
                        $arg->getAttributes(),
                        null
                    );
                }
            }
        }

        $this->replacePreviousArgs($node, $params, $keyParam, $newArgs);
        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     * @param Param[] $params
     * @param Arg[] $newArgs
     */
    private function replacePreviousArgs(Node $node, array $params, int $keyParam, array $newArgs): void
    {
        for ($i = $keyParam - 1; $i >= 0; --$i) {
            if (! isset($newArgs[$i]) && $params[$i]->default instanceof Expr) {
                $newArgs[$i] = new Arg($params[$i]->default);
            }
        }

        $countNewArgs = count($newArgs);
        for ($i = 0; $i < $countNewArgs; ++$i) {
            $node->args[$i] = $newArgs[$i];
        }
    }

    /**
     * @param Arg[] $args
     */
    private function shouldSkip(array $args): bool
    {
        if ($args === []) {
            return true;
        }

        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier) {
                return false;
            }
        }

        return true;
    }
}
