<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\LNumber;

use PhpParser\Node;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Taking the most generic use case to the account: https://wiki.php.net/rfc/numeric_literal_separator#should_it_be_the_role_of_an_ide_to_group_digits
 * The final check should be done manually
 *
 * @deprecated as opinionated and group size depends on context. Cannot be automated. Use manually where needed instead.
 */
final class AddLiteralSeparatorToNumberRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface, DeprecatedInterface
{
    /**
     * @api
     * @var string
     */
    public const LIMIT_VALUE = 'limit_value';
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add "_" as thousands separator in numbers for higher or equals to limitValue config', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $int = 500000;
        $float = 1000500.001;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $int = 500_000;
        $float = 1_000_500.001;
    }
}
CODE_SAMPLE
, [self::LIMIT_VALUE => 1000000])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Int_::class, Float_::class];
    }
    /**
     * @param Int_|Float_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated as opinionated and group size depends on context. Cannot be automated. Use manually where needed instead', self::class));
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::LITERAL_SEPARATOR;
    }
}
