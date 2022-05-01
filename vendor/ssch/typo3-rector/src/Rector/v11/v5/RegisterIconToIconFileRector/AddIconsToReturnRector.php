<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v5\RegisterIconToIconFileRector;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use stdClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @changelog https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Icon/Index.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v5\RegisterIconToIconFileRector\RegisterIconToIconFileRectorTest
 */
final class AddIconsToReturnRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ICON_IDENTIFIER = 'icon-identifier';
    /**
     * @var string
     */
    public const ICON_CONFIGURATION = 'icon-configuration';
    /**
     * @var string
     */
    private $iconIdentifier;
    /**
     * @var array<string, mixed>
     */
    private $iconConfiguration = [];
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add arguments to configure method in Symfony Command', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
return [];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'my-icon' => [
        'provider' => stdClass::class,
        'source' => 'mysvg.svg'
    ]
];
CODE_SAMPLE
, [self::ICON_IDENTIFIER => 'my-icon', self::ICON_CONFIGURATION => ['provider' => \stdClass::class, 'source' => 'mysvg.svg']])]);
    }
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->expr instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $iconArrayItem = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createArray($this->iconConfiguration), new \PhpParser\Node\Scalar\String_($this->iconIdentifier), \false, [\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS => [new \PhpParser\Comment(\PHP_EOL)]]);
        $node->expr->items[] = $iconArrayItem;
        return $node;
    }
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration) : void
    {
        $iconIdentifier = $configuration[self::ICON_IDENTIFIER] ?? '';
        $iconConfiguration = $configuration[self::ICON_CONFIGURATION] ?? [];
        \RectorPrefix20220501\Webmozart\Assert\Assert::stringNotEmpty($iconIdentifier);
        \RectorPrefix20220501\Webmozart\Assert\Assert::isArray($iconConfiguration);
        \RectorPrefix20220501\Webmozart\Assert\Assert::keyExists($iconConfiguration, 'provider');
        $this->iconConfiguration = $iconConfiguration;
        $this->iconIdentifier = $iconIdentifier;
    }
}
