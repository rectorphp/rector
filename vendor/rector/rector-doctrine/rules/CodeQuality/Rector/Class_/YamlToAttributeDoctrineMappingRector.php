<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Contract\DependencyInjection\RelatedConfigInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Doctrine\CodeQuality\AttributeTransformer\YamlToAttributeTransformer;
use Rector\Doctrine\CodeQuality\EntityMappingResolver;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202407\Webmozart\Assert\Assert;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Class_\YamlToAttributeDoctrineMappingRector\YamlToAttributeDoctrineMappingRectorTest
 */
final class YamlToAttributeDoctrineMappingRector extends AbstractRector implements ConfigurableRectorInterface, RelatedConfigInterface, MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Doctrine\CodeQuality\EntityMappingResolver
     */
    private $entityMappingResolver;
    /**
     * @readonly
     * @var \Rector\Doctrine\CodeQuality\AttributeTransformer\YamlToAttributeTransformer
     */
    private $yamlToAttributeTransformer;
    /**
     * @var string[]
     */
    private $yamlMappingDirectories = [];
    public function __construct(EntityMappingResolver $entityMappingResolver, YamlToAttributeTransformer $yamlToAttributeTransformer)
    {
        $this->entityMappingResolver = $entityMappingResolver;
        $this->yamlToAttributeTransformer = $yamlToAttributeTransformer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Converts YAML Doctrine Entity mapping to particular annotation mapping', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeEntity
{
    private $id;

    private $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SomeEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string')]
    private $name;
}

CODE_SAMPLE
, [__DIR__ . '/config/yaml_mapping_directory'])]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        if ($this->yamlMappingDirectories === []) {
            throw new ShouldNotHappenException('First, set directories with YAML entity mapping. Use $rectorConfig->ruleWithConfiguration() and pass paths as 2nd argument');
        }
        $entityMapping = $this->findEntityMapping($node);
        if (!$entityMapping instanceof EntityMapping) {
            return null;
        }
        $this->yamlToAttributeTransformer->transform($node, $entityMapping);
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString($configuration);
        Assert::allFileExists($configuration);
        $this->yamlMappingDirectories = $configuration;
    }
    public static function getConfigFile() : string
    {
        return DoctrineSetList::YAML_TO_ANNOTATIONS;
    }
    public function provideMinPhpVersion() : int
    {
        // required by Doctrine nested attributes
        return PhpVersion::PHP_81;
    }
    private function findEntityMapping(Class_ $class) : ?EntityMapping
    {
        $className = $this->getName($class);
        if (!\is_string($className)) {
            return null;
        }
        $entityMappings = $this->entityMappingResolver->resolveFromDirectories($this->yamlMappingDirectories);
        foreach ($entityMappings as $entityMapping) {
            if ($entityMapping->getClassName() !== $className) {
                continue;
            }
            return $entityMapping;
        }
        return null;
    }
}
