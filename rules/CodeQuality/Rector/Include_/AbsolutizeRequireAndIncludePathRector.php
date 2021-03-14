<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Include_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/symplify/CodingStandard#includerequire-should-be-followed-by-absolute-path
 *
 * @see \Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\AbsolutizeRequireAndIncludePathRectorTest
 */
final class AbsolutizeRequireAndIncludePathRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'include/require to absolute path. This Rector might introduce backwards incompatible code, when the include/require beeing changed depends on the current working directory.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        require 'autoload.php';

        require $variable;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        require __DIR__ . '/autoload.php';

        require $variable;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Include_::class];
    }

    /**
     * @param Include_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof String_) {
            return null;
        }

        /** @var string $includeValue */
        $includeValue = $this->valueResolver->getValue($node->expr);

        // skip phar and absolute paths
        if (Strings::startsWith($includeValue, 'phar://') || Strings::startsWith($includeValue, '/')) {
            return null;
        }

        // add preslash to string
        if (Strings::startsWith($includeValue, './')) {
            $node->expr->value = substr($includeValue, 1);
        } else {
            $node->expr->value = '/' . $includeValue;
        }

        $node->expr = new Concat(new Dir(), $node->expr);

        return $node;
    }
}
