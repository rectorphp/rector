<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\PathsAreNotTooLongRule;

use Iterator;
use PHPStan\Rules\Rule;
use Rector\PHPStanExtensions\Rule\PathsAreNotTooLongRule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;
use PhpParser\Node\Stmt\ClassLike;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PathsAreNotTooLongRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @param array<string|string[]> $expectedError
     */
    public function testRule(string $filePath, array $expectedError): void
    {
        $classLike = new ClassLike();
        $classLike->setAttribute(SmartFileInfo::class);

        // $this->assertSame("a", "a");
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            PathsAreNotTooLongRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}
