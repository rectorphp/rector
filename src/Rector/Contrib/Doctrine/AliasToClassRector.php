<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Doctrine;

use PhpParser\Node;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AliasToClassRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var string[]
     */
    private $aliasesToNamespaces = [];

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var MethodArgumentAnalyzer
     */
    private $methodArgumentAnalyzer;

    /**
     * @param string[] $aliasesToNamespaces
     */
    public function __construct(
        array $aliasesToNamespaces,
        MethodCallAnalyzer $methodCallAnalyzer,
        MethodArgumentAnalyzer $methodArgumentAnalyzer,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->aliasesToNamespaces = $aliasesToNamespaces;
        $this->nodeFactory = $nodeFactory;
        $this->methodArgumentAnalyzer = $methodArgumentAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces doctrine alias with class.', [
            new CodeSample('$em->getRepository("AppBundle:Post");', '$em->getRepository(\App\Entity\Post::class);'),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isMethod($node, 'getRepository')) {
            return false;
        }

        if (! $this->methodArgumentAnalyzer->isMethodNthArgumentString($node, 1)) {
            return false;
        }

        return $this->isAliasWithConfiguredEntity($node->args[0]->value->value);
    }

    public function refactor(Node $node): ?Node
    {
        $node->args[0]->value = $this->nodeFactory->createClassConstantReference(
            $this->convertAliasToFqn($node->args[0]->value->value)
        );

        return $node;
    }

    private function isAlias(string $name): bool
    {
        return strpos($name, ':') !== false;
    }

    private function hasAlias(string $name): bool
    {
        return isset($this->aliasesToNamespaces[strtok($name, ':')]);
    }

    private function isAliasWithConfiguredEntity(string $name): bool
    {
        return $this->isAlias($name) && $this->hasAlias($name);
    }

    private function convertAliasToFqn(string $name): string
    {
        [$namespaceAlias, $simpleClassName] = explode(':', $name, 2);

        return sprintf('%s\%s', $this->aliasesToNamespaces[$namespaceAlias], $simpleClassName);
    }
}
