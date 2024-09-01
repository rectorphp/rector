<?php

declare (strict_types=1);
namespace Rector\Php82\Rector\Param;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202409\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Php82\Rector\Param\AddSensitiveParameterAttributeRector\AddSensitiveParameterAttributeRectorTest
 */
final class AddSensitiveParameterAttributeRector extends AbstractRector implements ConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    protected $phpAttributeAnalyzer;
    public const SENSITIVE_PARAMETERS = 'sensitive_parameters';
    /**
     * @var string[]
     */
    private $sensitiveParameters = [];
    public function __construct(PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString($configuration[self::SENSITIVE_PARAMETERS] ?? []);
        $this->sensitiveParameters = (array) ($configuration[self::SENSITIVE_PARAMETERS] ?? []);
    }
    public function getNodeTypes() : array
    {
        return [Param::class];
    }
    /**
     * @param Node\Param $node
     */
    public function refactor(Node $node) : ?Param
    {
        if (!$this->isNames($node, $this->sensitiveParameters)) {
            return null;
        }
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($node, 'SensitiveParameter')) {
            return null;
        }
        $node->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified('SensitiveParameter'))]);
        return $node;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add SensitiveParameter attribute to method and function configured parameters', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string $password)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(#[\SensitiveParameter] string $password)
    {
    }
}
CODE_SAMPLE
, [self::SENSITIVE_PARAMETERS => ['password']])]);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SENSITIVE_PARAMETER_ATTRIBUTE;
    }
}
