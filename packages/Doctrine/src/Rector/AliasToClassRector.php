<?php declare(strict_types=1);

namespace Rector\Doctrine\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AliasToClassRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $aliasesToNamespaces = [];

    /**
     * @var string
     */
    private $entityManagerClass;

    /**
     * @param string[] $aliasesToNamespaces
     */
    public function __construct(
        array $aliasesToNamespaces,
        string $entityManagerClass = 'Doctrine\ORM\EntityManagerInterface'
    ) {
        $this->aliasesToNamespaces = $aliasesToNamespaces;
        $this->entityManagerClass = $entityManagerClass;
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
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, $this->entityManagerClass)) {
            return null;
        }

        if (! $this->isName($node, 'getRepository')) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        if (! $node->args[0]->value instanceof String_) {
            return null;
        }

        /** @var String_ $stringNode */
        $stringNode = $node->args[0]->value;
        if (! $this->isAliasWithConfiguredEntity($stringNode->value)) {
            return null;
        }

        $node->args[0]->value = $this->createClassConstantReference(
            $this->convertAliasToFqn($node->args[0]->value->value)
        );

        return $node;
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

    private function isAlias(string $name): bool
    {
        return strpos($name, ':') !== false;
    }

    private function hasAlias(string $name): bool
    {
        return isset($this->aliasesToNamespaces[strtok($name, ':')]);
    }
}
