<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\NodeVisitor;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use Rector\Exception\NotImplementedYetException;
use Rector\Symfony\Configs\NodeAnalyser\SetServiceClassNameResolver;
use Rector\Symfony\Configs\ValueObject\ServiceArguments;
final class CollectServiceArgumentsNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private const ENVS = 'envs';
    /**
     * @var string
     */
    private const PARAMETERS = 'parameters';
    /**
     * @var array<string, array<self::ENVS|self::PARAMETERS, array<string|Expr>>>
     */
    private array $servicesArgumentsByClass = [];
    /**
     * @readonly
     */
    private SetServiceClassNameResolver $setServiceClassNameResolver;
    public function __construct()
    {
        $this->setServiceClassNameResolver = new SetServiceClassNameResolver();
    }
    /**
     * @param Stmt[] $nodes
     */
    public function beforeTraverse(array $nodes)
    {
        $this->servicesArgumentsByClass = [];
        return parent::beforeTraverse($nodes);
    }
    public function enterNode(Node $node) : ?Node
    {
        $argsMethodCall = $this->matchNamedMethodCall($node, 'args');
        if ($argsMethodCall instanceof MethodCall) {
            $this->processArgsMethodCall($argsMethodCall);
            return null;
        }
        $argMethodCall = $this->matchNamedMethodCall($node, 'arg');
        if (!$argMethodCall instanceof MethodCall) {
            return null;
        }
        // 1. detect arg name + value
        $firstArg = $argMethodCall->getArgs()[0];
        if ($firstArg->value instanceof String_ || $firstArg->value instanceof Int_) {
            $argumentLocation = $firstArg->value->value;
            if (\is_string($argumentLocation)) {
                // remove $ prefix
                $argumentLocation = \ltrim($argumentLocation, '$');
            }
        } else {
            throw new NotImplementedYetException(\sprintf('Add support for non-string arg names like "%s"', \get_class($firstArg->value)));
        }
        $serviceClassName = $this->setServiceClassNameResolver->resolve($argMethodCall);
        if (!\is_string($serviceClassName)) {
            return null;
        }
        $secondArg = $argMethodCall->getArgs()[1];
        if ($secondArg->value instanceof Concat) {
            $unwrappedExpr = $this->matchConcatWrappedParameter($secondArg->value);
            if (!$unwrappedExpr instanceof Expr) {
                return null;
            }
            $this->servicesArgumentsByClass[$serviceClassName][self::PARAMETERS][$argumentLocation] = $unwrappedExpr;
            return null;
        }
        if ($secondArg->value instanceof String_) {
            $argumentValue = $secondArg->value->value;
        } else {
            throw new NotImplementedYetException(\sprintf('Add support for non-string arg values like "%s"', \get_class($firstArg->value)));
        }
        $this->matchStringEnvOrParameter($argumentValue, $serviceClassName, $argumentLocation);
        return $node;
    }
    /**
     * @return ServiceArguments[]
     */
    public function getServicesArguments() : array
    {
        $serviceArguments = [];
        foreach ($this->servicesArgumentsByClass as $serviceClass => $arguments) {
            $parameters = $arguments[self::PARAMETERS] ?? [];
            $envs = $arguments[self::ENVS] ?? [];
            $serviceArguments[] = new ServiceArguments($serviceClass, $parameters, $envs);
        }
        return $serviceArguments;
    }
    /**
     * We look for: ->arg(..., ...)
     */
    private function matchNamedMethodCall(Node $node, string $methodName) : ?MethodCall
    {
        if (!$node instanceof MethodCall) {
            return null;
        }
        if (!$node->name instanceof Identifier) {
            return null;
        }
        if ($node->name->toString() !== $methodName) {
            return null;
        }
        return $node;
    }
    /**
     * We look for:
     * "%" . ParameterName::NAME . "%"
     */
    private function matchConcatWrappedParameter(Concat $concat) : ?Expr
    {
        // special case for concat parameter enum const
        if (!$concat->right instanceof String_) {
            return null;
        }
        if ($concat->right->value !== '%') {
            return null;
        }
        $nestedConcat = $concat->left;
        if (!$nestedConcat instanceof Concat) {
            return null;
        }
        if (!$nestedConcat->left instanceof String_) {
            return null;
        }
        if ($nestedConcat->left->value !== '%') {
            return null;
        }
        return $nestedConcat->right;
    }
    /**
     * @param int|string $argumentLocation
     */
    private function matchStringEnvOrParameter(string $argumentValue, string $serviceClassName, $argumentLocation) : void
    {
        $match = Strings::match($argumentValue, '#%env\\((?<env>[A-Z_]+)\\)#');
        if (isset($match['env'])) {
            $this->servicesArgumentsByClass[$serviceClassName][self::ENVS][$argumentLocation] = (string) $match['env'];
        }
        $match = Strings::match($argumentValue, '#%(?<parameter>[\\w]+)%#');
        if (isset($match['parameter'])) {
            $this->servicesArgumentsByClass[$serviceClassName][self::PARAMETERS][$argumentLocation] = (string) $match['parameter'];
        }
    }
    private function processArgsMethodCall(MethodCall $argsMethodCall) : void
    {
        $serviceClassName = $this->setServiceClassNameResolver->resolve($argsMethodCall);
        // unable to resolve service
        if (!\is_string($serviceClassName)) {
            return;
        }
        // collect all
        $firstArg = $argsMethodCall->getArgs()[0];
        // must be an array
        if (!$firstArg->value instanceof Array_) {
            return;
        }
        foreach ($firstArg->value->items as $position => $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            // not a string? most likely services reference or something else
            if (!$arrayItem->value instanceof String_) {
                continue;
            }
            $this->matchStringEnvOrParameter($arrayItem->value->value, $serviceClassName, $position);
        }
    }
}
