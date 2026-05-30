<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony81\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/8.1/UPGRADE-8.1.md#filesystem
 *
 * @see \Rector\Symfony\Tests\Symfony81\Rector\MethodCall\RenameCopyOnWindowsOptionToFollowSymlinksRector\RenameCopyOnWindowsOptionToFollowSymlinksRectorTest
 */
final class RenameCopyOnWindowsOptionToFollowSymlinksRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename copy_on_windows option to follow_symlinks in Filesystem::mirror()', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Filesystem\Filesystem;

final class Foo
{
    public function __construct(private Filesystem $filesystem) {}

    public function bar($originDir, $targetDir): void
    {
        $this->filesystem->mirror(targetDir: $originDir, originDir: $targetDir, options: $options = ['copy_on_windows' => true]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Filesystem\Filesystem;

final class Foo
{
    public function __construct(private Filesystem $filesystem) {}

    public function bar($originDir, $targetDir): void
    {
        $this->filesystem->mirror(targetDir: $originDir, originDir: $targetDir, options: $options = ['follow_symlinks' => true]);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isObjectType($node->var, new ObjectType('Symfony\Component\Filesystem\Filesystem'))) {
            return null;
        }
        if (!$this->isName($node->name, 'mirror')) {
            return null;
        }
        $optionsArg = null;
        foreach ($node->args as $index => $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }
            if ($arg->name instanceof Identifier && $arg->name->toString() === 'options') {
                $optionsArg = $arg;
                break;
            }
            if (!$arg->name instanceof Identifier && $index === 3) {
                $optionsArg = $arg;
                break;
            }
        }
        if (!$optionsArg instanceof Arg) {
            return null;
        }
        $options = $optionsArg->value;
        if ($options instanceof Assign) {
            $options = $options->expr;
        }
        if (!$options instanceof Array_) {
            return null;
        }
        $hasChanged = \false;
        foreach ($options->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if (!$item->key instanceof String_) {
                continue;
            }
            if ($item->key->value !== 'copy_on_windows') {
                continue;
            }
            $item->key = new String_('follow_symlinks');
            $hasChanged = \true;
        }
        return $hasChanged ? $node : null;
    }
}
