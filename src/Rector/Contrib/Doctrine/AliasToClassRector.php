<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Doctrine;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
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
     * @param string[] $aliasesToNamespaces
     */
    public function __construct(
        array $aliasesToNamespaces,
        MethodCallAnalyzer $methodCallAnalyzer,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->aliasesToNamespaces = $aliasesToNamespaces;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isMethod($node, 'getRepository')) {
            return false;
        }

        $className = $node->getAttribute(Attribute::CLASS_NAME);

        if ($className === null) {
            return false;
        }

        /** @var MethodCall $node */
        if (count($node->args) !== 1) {
            return false;
        }

        if (! $node->args[0]->value instanceof String_) {
            return false;
        }

        /** @var String_ $string */
        $string = $node->args[0]->value;

        return $this->isAlias($string->value) && $this->entityFqn($string->value) !== '';
    }

    public function refactor(Node $node): ?Node
    {
        $node->args[0]->value = $this->nodeFactory->createClassConstantReference(
            $this->entityFqn($node->args[0]->value->value)
        );

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces doctrine alias with class.', [
            new CodeSample('$em->getRepository("AppBundle:Post");', '$em->getRepository(\App\Entity\Post::class);'),
        ]);
    }

    private function isAlias(string $name): bool
    {
        return strpos($name, ':') !== false;
    }

    private function entityFqn(string $name): string
    {
        [$namespaceAlias, $simpleClassName] = explode(':', $name, 2);

        if (array_key_exists($namespaceAlias, $this->aliasesToNamespaces)) {
            return sprintf('%s\%s', $this->aliasesToNamespaces[$namespaceAlias], $simpleClassName);
        }

        return '';
    }
}
