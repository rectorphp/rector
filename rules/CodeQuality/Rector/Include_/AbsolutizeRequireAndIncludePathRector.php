<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Include_;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Util\StringUtils;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\AbsolutizeRequireAndIncludePathRectorTest
 */
final class AbsolutizeRequireAndIncludePathRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @var string
     * @see https://regex101.com/r/N8oLqv/1
     */
    private const WINDOWS_DRIVE_REGEX = '#^[a-zA-z]\\:[\\/\\\\]#';
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('include/require to absolute path. This Rector might introduce backwards incompatible code, when the include/require being changed depends on the current working directory.', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        require 'autoload.php';

        require $variable;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        require __DIR__ . '/autoload.php';

        require $variable;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Include_::class];
    }
    /**
     * @param Include_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->expr instanceof Concat && $node->expr->left instanceof String_ && $this->isRefactorableStringPath($node->expr->left)) {
            $node->expr->left = $this->prefixWithDirConstant($node->expr->left);
            return $node;
        }
        if (!$node->expr instanceof String_) {
            return null;
        }
        if (!$this->isRefactorableStringPath($node->expr)) {
            return null;
        }
        /** @var string $includeValue */
        $includeValue = $this->valueResolver->getValue($node->expr);
        // skip phar
        if (\strncmp($includeValue, 'phar://', \strlen('phar://')) === 0) {
            return null;
        }
        // skip absolute paths
        if (\strncmp($includeValue, '/', \strlen('/')) === 0 || \strncmp($includeValue, '\\', \strlen('\\')) === 0) {
            return null;
        }
        if (\strpos($includeValue, 'config/') !== \false) {
            return null;
        }
        if (StringUtils::isMatch($includeValue, self::WINDOWS_DRIVE_REGEX)) {
            return null;
        }
        // add preslash to string
        $node->expr->value = \strncmp($includeValue, './', \strlen('./')) === 0 ? Strings::substring($includeValue, 1) : '/' . $includeValue;
        $node->expr = $this->prefixWithDirConstant($node->expr);
        return $node;
    }
    private function isRefactorableStringPath(String_ $string) : bool
    {
        return \strncmp($string->value, 'phar://', \strlen('phar://')) !== 0;
    }
    private function prefixWithDirConstant(String_ $string) : Concat
    {
        $this->removeExtraDotSlash($string);
        $this->prependSlashIfMissing($string);
        return new Concat(new Dir(), $string);
    }
    /**
     * Remove "./" which would break the path
     */
    private function removeExtraDotSlash(String_ $string) : void
    {
        if (\strncmp($string->value, './', \strlen('./')) !== 0) {
            return;
        }
        $string->value = Strings::replace($string->value, '#^\\.\\/#', '/');
    }
    private function prependSlashIfMissing(String_ $string) : void
    {
        if (\strncmp($string->value, '/', \strlen('/')) === 0) {
            return;
        }
        $string->value = '/' . $string->value;
    }
}
