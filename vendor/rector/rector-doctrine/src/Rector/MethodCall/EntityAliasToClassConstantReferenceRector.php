<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202305\Webmozart\Assert\Assert;
/**
 * @see \Rector\Doctrine\Tests\Rector\MethodCall\EntityAliasToClassConstantReferenceRector\EntityAliasToClassConstantReferenceRectorTest
 */
final class EntityAliasToClassConstantReferenceRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const ALIASES_TO_NAMESPACES = 'aliases_to_namespaces';
    /**
     * @var ObjectType[]
     */
    private $doctrineManagerRegistryObjectTypes = [];
    /**
     * @var array<string, string>
     */
    private $aliasesToNamespaces = [];
    public function __construct()
    {
        $this->doctrineManagerRegistryObjectTypes = [new ObjectType('Doctrine\\ORM\\EntityManagerInterface'), new ObjectType('Doctrine\\Persistence\\ObjectManager'), new ObjectType('Doctrine\\Common\\Persistence\\ObjectManager'), new ObjectType('Doctrine\\Persistence\\ManagerRegistry'), new ObjectType('Doctrine\\Common\\Persistence\\ManagerRegistry')];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces doctrine alias with class.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$entityManager = new Doctrine\ORM\EntityManager();
$entityManager->getRepository("AppBundle:Post");
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$entityManager = new Doctrine\ORM\EntityManager();
$entityManager->getRepository(\App\Entity\Post::class);
CODE_SAMPLE
, [self::ALIASES_TO_NAMESPACES => ['App' => 'App\\Entity']])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isObjectTypes($node->var, $this->doctrineManagerRegistryObjectTypes)) {
            return null;
        }
        if (!$this->isName($node->name, 'getRepository')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0]->value instanceof String_) {
            return null;
        }
        /** @var String_ $stringNode */
        $stringNode = $node->args[0]->value;
        if (!$this->isAliasWithConfiguredEntity($stringNode->value)) {
            return null;
        }
        $node->args[0]->value = $this->nodeFactory->createClassConstReference($this->convertAliasToFqn($node->args[0]->value->value));
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $aliasesToNamespaces = $configuration[self::ALIASES_TO_NAMESPACES] ?? $configuration;
        Assert::isArray($aliasesToNamespaces);
        Assert::allString(\array_keys($aliasesToNamespaces));
        Assert::allString($aliasesToNamespaces);
        $this->aliasesToNamespaces = $aliasesToNamespaces;
    }
    private function isAliasWithConfiguredEntity(string $name) : bool
    {
        if (!$this->isAlias($name)) {
            return \false;
        }
        return $this->hasAlias($name);
    }
    private function convertAliasToFqn(string $name) : string
    {
        [$namespaceAlias, $simpleClassName] = \explode(':', $name, 2);
        return \sprintf('%s\\%s', $this->aliasesToNamespaces[$namespaceAlias], $simpleClassName);
    }
    private function isAlias(string $name) : bool
    {
        return \strpos($name, ':') !== \false;
    }
    private function hasAlias(string $name) : bool
    {
        return isset($this->aliasesToNamespaces[\strtok($name, ':')]);
    }
}
