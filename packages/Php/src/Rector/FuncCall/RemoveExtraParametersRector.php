<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Parser;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * @see https://www.reddit.com/r/PHP/comments/a1ie7g/is_there_a_linter_for_argumentcounterror_for_php/
 * @see http://php.net/manual/en/class.argumentcounterror.php
 */
final class RemoveExtraParametersRector extends AbstractRector
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(BetterNodeFinder $betterNodeFinder, Parser $parser)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->parser = $parser;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove extra parameters', [
            new CodeSample('strlen("asdf", 1);', 'strlen("asdf");'),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, MethodCall::class, StaticCall::class];
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->args) === 0) {
            return null;
        }

        $reflectionFunctionLike = $this->resolveReflectionFunctionLike($node);

        // can be any number of arguments → nothing to limit here
        if ($this->isVariadic($reflectionFunctionLike, $node)) {
            return null;
        }

        if ($reflectionFunctionLike->getNumberOfParameters() >= count($node->args)) {
            return null;
        }

        for ($i = $reflectionFunctionLike->getNumberOfParameters(); $i < count($node->args); $i++) {
            unset($node->args[$i]);
        }

        // re-index for printer
        $node->args = array_values($node->args);

        return $node;
    }

    private function resolveReflectionFunctionLike(Node $node): ReflectionFunctionAbstract
    {
        if ($node instanceof FuncCall) {
            return new ReflectionFunction($this->getName($node));
        }

        $className = $node->getAttribute(Attribute::CLASS_NAME);
        return new ReflectionMethod($className, $this->getName($node));
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    private function isVariadic(ReflectionFunctionAbstract $reflectionFunctionAbstract): bool
    {
        // detects from ... in parameters
        if ($reflectionFunctionAbstract->isVariadic()) {
            return true;
        }

        // external function/method
        $nodes = $this->parser->parse(file_get_contents($reflectionFunctionAbstract->getFileName()));

        $removeFunctionLikeNode = $this->betterNodeFinder->find($nodes, function (Node $node) use ($reflectionFunctionAbstract) {
            if ($reflectionFunctionAbstract instanceof ReflectionFunction) {
                if ($node instanceof Function_) {
                    if ($this->isName($node, $reflectionFunctionAbstract->getName())) {
                        return true;
                    }
                }
            } else {
                if ($node instanceof ClassMethod) {
                    if ($this->isName($node, $reflectionFunctionAbstract->getName())) {
                        return true;
                    }
                }
            }

            return null;
        });

        // detect from "func_*_arg(s)" calls in the body
        return (bool) $this->betterNodeFinder->findFirst($removeFunctionLikeNode, function (Node $node) {
            if (! $node instanceof FuncCall) {
                return null;
            }

            if ($this->isNames($node, ['func_get_args', 'func_get_arg', 'func_num_args'])) {
                return true;
            }

            return null;
        });
    }
}
