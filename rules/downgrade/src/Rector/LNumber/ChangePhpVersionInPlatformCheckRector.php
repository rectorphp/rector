<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\LNumber;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\PhpVersionFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;
use Webmozart\Assert\Assert;

/**
 * @see https://php.watch/articles/composer-platform-check
 * @see https://getcomposer.org/doc/06-config.md#platform-check
 *
 * @see \Rector\Downgrade\Tests\Rector\LNumber\ChangePhpVersionInPlatformCheckRector\ChangePhpVersionInPlatformCheckRectorTest
 */
final class ChangePhpVersionInPlatformCheckRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TARGET_PHP_VERSION = 'target_php_version';

    /**
     * @see https://regex101.com/r/oVWPoe/1/
     * @var string
     */
    private const PHP_VERSION_REGEX = '#(?<sign>>=|>) (?<version>\d\.\d\.\d)#';

    /**
     * @var int
     */
    private $targetPhpVersion;

    /**
     * @var PhpVersionFactory
     */
    private $phpVersionFactory;

    public function __construct(PhpVersionFactory $phpVersionFactory)
    {
        $this->phpVersionFactory = $phpVersionFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `vendor/composer/platform_check.php` to desired minimal PHP version', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$issues = [];

if (!(PHP_VERSION_ID >= 70300)) {
    $issues[] = 'Your Composer dependencies require a PHP version ">= 7.3.0". You are running ' . PHP_VERSION  .  '.';
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
$issues = [];

if (!(PHP_VERSION_ID >= 70100)) {
    $issues[] = 'Your Composer dependencies require a PHP version ">= 7.1.0". You are running ' . PHP_VERSION  .  '.';
}
CODE_SAMPLE
,
                [
                    self::TARGET_PHP_VERSION => 70100,
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [LNumber::class, String_::class];
    }

    /**
     * @param LNumber|String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isPlatformCheckFile($node)) {
            return null;
        }

        if ($node instanceof LNumber) {
            return $this->refactorLNumber($node);
        }

        if ($node instanceof String_) {
            return $this->refactorString($node);
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $targetPhpVersion = $configuration[self::TARGET_PHP_VERSION] ?? null;
        Assert::integer($targetPhpVersion);

        $this->targetPhpVersion = $targetPhpVersion;
    }

    private function isPlatformCheckFile(Node $node): bool
    {
        $fileInfo = $node->getAttribute(SmartFileInfo::class);
        if (! $fileInfo instanceof SmartFileInfo) {
            return false;
        }

        return Strings::endsWith($fileInfo->getFilename(), 'platform_check.php');
    }

    private function refactorLNumber(LNumber $lNumber): ?LNumber
    {
        if (Strings::length((string) $lNumber->value) !== 5) {
            return null;
        }

        $parent = $lNumber->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Greater && ! $parent instanceof GreaterOrEqual) {
            return null;
        }

        return new LNumber($this->targetPhpVersion);
    }

    private function refactorString(String_ $string): ?String_
    {
        $match = Strings::match($string->value, self::PHP_VERSION_REGEX);
        if ($match === null) {
            return null;
        }

        $stringPhpVersion = $this->phpVersionFactory->createStringVersion($this->targetPhpVersion);
        $changedContent = Strings::replace($string->value, self::PHP_VERSION_REGEX, function (array $match) use (
            $stringPhpVersion
        ): string {
            return $match['sign'] . ' ' . $stringPhpVersion;
        });

        if ($string->value === $changedContent) {
            return null;
        }

        return new String_($changedContent);
    }
}
