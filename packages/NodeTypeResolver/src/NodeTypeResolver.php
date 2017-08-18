<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use SplObjectStorage;

final class NodeTypeResolver
{
    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    /**
     * @var Standard
     */
    private $prettyPrinter;

    /**
     * @var TypeSpecifier
     */
    private $typeSpecifier;

    /**
     * @var SplObjectStorage|Scope[]
     */
    private $nodeWithScope = [];

    /**
     * @var Broker
     */
    private $broker;

    public function __construct(
        NodeScopeResolver $nodeScopeResolver,
        Standard $prettyPrinter,
        TypeSpecifier $typeSpecifier
    ) {
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->prettyPrinter = $prettyPrinter;
        $this->typeSpecifier = $typeSpecifier;
        $this->nodeWithScope = new SplObjectStorage;
    }

    public function setBroker(Broker $broker): void
    {
        $this->broker = $broker;
    }

    /**
     * @param Node[] $nodes
     */
    public function getTypeForNode(Node $node, array $nodes): Scope
    {
        if (! isset($this->nodeWithScope[$node])) {
            $this->prepareForNodes($nodes);
        }

        // variable
        $scope = $this->nodeWithScope[$node];
        dump($scope->getType($node));

        // should be 'Nette\Utils\Html'
        die;

        return $this->nodeWithScope[$node];
    }

    /**
     * @param Node[] $nodes
     */
    private function prepareForNodes(array $nodes): void
    {
        $this->nodeScopeResolver->processNodes($nodes, $this->createScope(), function (Node $node, Scope $scope) {
            $this->nodeWithScope[$node] = $scope;
        });
    }

    private function createScope(): Scope
    {
        return new Scope($this->broker, $this->prettyPrinter, $this->typeSpecifier, '');
    }
}
