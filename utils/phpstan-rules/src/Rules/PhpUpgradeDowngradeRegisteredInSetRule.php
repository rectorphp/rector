<?php

declare(strict_types=1);

namespace Rector\PHPStanRules\Rules;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Symplify\PHPStanRules\Rules\AbstractSymplifyRule;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * @see \Rector\PHPStanRules\Tests\Rules\PhpUpgradeDowngradeRegisteredInSetRule\PhpUpgradeDowngradeRegisteredInSetRuleTest
 */
final class PhpUpgradeDowngradeRegisteredInSetRule extends AbstractSymplifyRule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Register %s to %s config set';

    /**
     * @var string
     * @see https://regex101.com/r/C3nz6e/1/
     */
    private const PREFIX_REGEX = '#(Downgrade)?Php\d+#';

    public function __construct(
        private SmartFileSystem $smartFileSystem
    ) {
    }

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

        [, $prefix] = explode('\\', $className);
        if (! Strings::match($prefix, self::PREFIX_REGEX)) {
            return [];
        }

        $phpVersion = Strings::substring($prefix, -2);

        $configFile = str_starts_with($prefix, 'Downgrade')
            ? 'downgrade-php' . $phpVersion
            : 'php' . $phpVersion;

        $configContent = $this->smartFileSystem->readFile(
            __DIR__ . '/../../../../config/set/' . $configFile . '.php'
        );

        $shortClassName = (string) $node->name;
        $toSearch = sprintf('$services->set(%s::class)', $shortClassName);

        if (! str_contains($configContent, $toSearch)) {
            return [sprintf(self::ERROR_MESSAGE, $className, $configFile)];
        }

        return [];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
// config/set/php74.php
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
// config/set/php74.php
$services->set(RealToFloatTypeCastRector::class);
CODE_SAMPLE
            ),
        ]);
    }
}
