<?php declare(strict_types=1);

namespace Rector\Tests\NodeVisitor\UpgradeDeprecation\ReplaceDeprecatedConstantNodeVisitor;

use Rector\NodeVisitor\UpgradeDeprecation\AbstraceReplaceDeprecatedConstantNodeVisitor;

final class ReplaceOldConstantNodeVisitor extends AbstraceReplaceDeprecatedConstantNodeVisitor
{
    public function getClassName(): string
    {
        return'ClassWithConstants';
    }

    public function getOldConstantName(): string
    {
        return 'OLD_CONSTANT';
    }

    public function getNewConstantName(): string
    {
        return 'NEW_CONSTANT';
    }
}
