<?php declare(strict_types=1);

namespace Rector\Doctrine\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use function Safe\sprintf;

final class AliasToClassRector extends AbstractRector
{
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
        MethodArgumentAnalyzer $methodArgumentAnalyzer,
        NodeFactory $nodeFactory
    ) {
        $this->aliasesToNamespaces = $aliasesToNamespaces;
        $this->nodeFactory = $nodeFactory;
        $this->methodArgumentAnalyzer = $methodArgumentAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces doctrine alias with class.', [
            new CodeSample(
<<<'CODE_SAMPLE'
$entityManager = new Doctrine\ORM\EntityManager();
$entityManager->getRepository("AppBundle:Post");
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$entityManager = new Doctrine\ORM\EntityManager();
$entityManager->getRepository(\App\Entity\Post::class);
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $methodCall
     */
    public function refactor(Node $methodCall): ?Node
    {
        if (! $this->isName($methodCall, 'getRepository')) {
            return null;
        }

        if (! $this->methodArgumentAnalyzer->isMethodNthArgumentString($methodCall, 1)) {
            return null;
        }
        /** @var MethodCall $methodCall */
        $methodCall = $methodCall;
        if ($this->isAliasWithConfiguredEntity($methodCall->args[0]->value->value) === false) {
            return null;
        }
        $methodCall->args[0]->value = $this->nodeFactory->createClassConstantReference(
            $this->convertAliasToFqn($methodCall->args[0]->value->value)
        );

        return $methodCall;
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
