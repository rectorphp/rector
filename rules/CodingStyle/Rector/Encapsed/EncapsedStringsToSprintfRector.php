<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Encapsed;

use PhpParser\Node;
use PhpParser\Node\Scalar\InterpolatedString;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as turning "{$string}" interpolation into sprintf() or concat is a matter of personal preference. It can worsen readability and is rather a coding standard change.
 */
final class EncapsedStringsToSprintfRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    /**
     * @api
     * @var string
     */
    public const ALWAYS = 'always';
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert enscaped {$string} to more readable sprintf or concat, if no mask is used', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
echo "Unsupported format {$format} - use another";

echo "Try {$allowed}";
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo sprintf('Unsupported format %s - use another', $format);

echo 'Try ' . $allowed;
CODE_SAMPLE
, [self::ALWAYS => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [InterpolatedString::class];
    }
    /**
     * @param InterpolatedString $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as converting string interpolation to sprintf() is a personal preference that can worsen readability', self::class));
    }
}
