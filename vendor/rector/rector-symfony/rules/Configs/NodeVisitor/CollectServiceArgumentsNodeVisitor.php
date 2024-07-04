<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\NodeVisitor;

use RectorPrefix202407\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
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
     * @var array<string, array<self::ENVS|self::PARAMETERS, string[]>>
     */
    private $servicesArgumentsByClass = [];
    /**
     * @readonly
     * @var \Rector\Symfony\Configs\NodeAnalyser\SetServiceClassNameResolver
     */
    private $setServiceClassNameResolver;
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
        $argMethodCall = $this->matchArgMethodCall($node);
        if (!$argMethodCall instanceof MethodCall) {
            return null;
        }
        // 1. detect arg name + value
        $firstArg = $argMethodCall->getArgs()[0];
        if ($firstArg->value instanceof String_ || $firstArg->value instanceof Node\Scalar\LNumber) {
            $argumentLocator = $firstArg->value->value;
        } else {
            throw new NotImplementedYetException(\sprintf('Add support for non-string arg names like "%s"', \get_class($firstArg->value)));
        }
        $secondArg = $argMethodCall->getArgs()[1];
        if (!$secondArg->value instanceof String_) {
            throw new NotImplementedYetException(\sprintf('Add support for non-string arg values like "%s"', \get_class($firstArg->value)));
        }
        $serviceClassName = $this->setServiceClassNameResolver->resolve($argMethodCall);
        if (!\is_string($serviceClassName)) {
            return null;
        }
        $argumentValue = $secondArg->value->value;
        $match = Strings::match($argumentValue, '#%env\\((?<env>[A-Z_]+)\\)#');
        if (isset($match['env'])) {
            $this->servicesArgumentsByClass[$serviceClassName][self::ENVS][$argumentLocator] = (string) $match['env'];
            return null;
        }
        $match = Strings::match($argumentValue, '#%(?<parameter>[\\w]+)%#');
        if (isset($match['parameter'])) {
            $this->servicesArgumentsByClass[$serviceClassName][self::PARAMETERS][$argumentLocator] = (string) $match['parameter'];
            return null;
        }
        return null;
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
    private function matchArgMethodCall(Node $node) : ?MethodCall
    {
        if (!$node instanceof MethodCall) {
            return null;
        }
        if (!$node->name instanceof Identifier) {
            return null;
        }
        if ($node->name->toString() !== 'arg') {
            return null;
        }
        return $node;
    }
}
