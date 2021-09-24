<?php

declare(strict_types=1);

namespace Rector\PHPStanRules\Rules;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Symplify\PHPStanRules\Rules\AbstractSymplifyRule;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PHPStanRules\Tests\Rules\PhpUpgradeImplementsMinPhpVersionInterfaceRule\PhpUpgradeImplementsMinPhpVersionInterfaceRuleTest
 */
final class PhpUpgradeImplementsMinPhpVersionInterfaceRule extends AbstractSymplifyRule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Rule %s must implements Rector\VersionBonding\Contract\MinPhpVersionInterface';

    /**
     * @var string
     * @see https://regex101.com/r/9d3jGP/2/
     */
    private const PREFIX_REGEX = '#\\\\Php\d+\\\\#';

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     * @return string[]
     */
    public function process(Node $node, Scope $scope): array
    {
        /** @var string $className */
        $className = (string) $node->namespacedName;
        if (! str_ends_with($className, 'Rector')) {
            return [];
        }

        if (! Strings::match($className, self::PREFIX_REGEX)) {
            return [];
        }

        $implements = $node->implements;
        foreach ($implements as $implement) {
            if (! $implement instanceof FullyQualified) {
                continue;
            }

            if ($implement->toString() !== 'Rector\VersionBonding\Contract\MinPhpVersionInterface') {
                continue;
            }

            return [];
        }

        return [sprintf(self::ERROR_MESSAGE, $className)];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
namespace Rector\Php80\Rector\Switch_;

final class ChangeSwitchToMatchRector extends AbstractRector
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
namespace Rector\Php80\Rector\Switch_;

use Rector\VersionBonding\Contract\MinPhpVersionInterface;

final class ChangeSwitchToMatchRector extends AbstractRector implements MinPhpVersionInterface
{
}
CODE_SAMPLE
            ),
        ]);
    }
}
