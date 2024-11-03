<?php

declare (strict_types=1);
namespace Rector\Php55\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\NodeTraverser;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\StringClassNameToClassConstantRectorTest
 */
final class StringClassNameToClassConstantRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var string
     */
    public const SHOULD_KEEP_PRE_SLASH = 'should_keep_pre_slash';
    /**
     * @var string
     */
    private const IS_UNDER_CLASS_CONST = 'is_under_class_const';
    /**
     * @var string[]
     */
    private $classesToSkip = [];
    /**
     * @var bool
     */
    private $shouldKeepPreslash = \false;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace string class names by <class>::class constant', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class AnotherClass
{
}

class SomeClass
{
    public function run()
    {
        return 'AnotherClass';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class AnotherClass
{
}

class SomeClass
{
    public function run()
    {
        return \AnotherClass::class;
    }
}
CODE_SAMPLE
, ['ClassName', 'AnotherClassName', self::SHOULD_KEEP_PRE_SLASH => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [String_::class, FuncCall::class, ClassConst::class];
    }
    /**
     * @param String_|FuncCall|ClassConst $node
     * @return \PhpParser\Node\Expr\BinaryOp\Concat|\PhpParser\Node\Expr\ClassConstFetch|null|int
     */
    public function refactor(Node $node)
    {
        // allow class strings to be part of class const arrays, as probably on purpose
        if ($node instanceof ClassConst) {
            $this->decorateClassConst($node);
            return null;
        }
        // keep allowed string as condition
        if ($node instanceof FuncCall) {
            if ($this->isName($node, 'is_a')) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            return null;
        }
        if ($node->getAttribute(self::IS_UNDER_CLASS_CONST) === \true) {
            return null;
        }
        $classLikeName = $node->value;
        // remove leading slash
        $classLikeName = \ltrim($classLikeName, '\\');
        if ($classLikeName === '') {
            return null;
        }
        if ($this->shouldSkip($classLikeName)) {
            return null;
        }
        $fullyQualified = new FullyQualified($classLikeName);
        if ($this->shouldKeepPreslash && $classLikeName !== $node->value) {
            $preSlashCount = \strlen($node->value) - \strlen($classLikeName);
            $preSlash = \str_repeat('\\', $preSlashCount);
            $string = new String_($preSlash);
            return new Concat($string, new ClassConstFetch($fullyQualified, 'class'));
        }
        return new ClassConstFetch($fullyQualified, 'class');
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        if (isset($configuration[self::SHOULD_KEEP_PRE_SLASH]) && \is_bool($configuration[self::SHOULD_KEEP_PRE_SLASH])) {
            $this->shouldKeepPreslash = $configuration[self::SHOULD_KEEP_PRE_SLASH];
            unset($configuration[self::SHOULD_KEEP_PRE_SLASH]);
        }
        Assert::allString($configuration);
        $this->classesToSkip = $configuration;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::CLASSNAME_CONSTANT;
    }
    private function shouldSkip(string $classLikeName) : bool
    {
        // skip short class names, mostly invalid use of strings
        if (\strpos($classLikeName, '\\') === \false) {
            return \true;
        }
        // possibly string
        if (\ctype_lower($classLikeName[0])) {
            return \true;
        }
        if (!$this->reflectionProvider->hasClass($classLikeName)) {
            return \true;
        }
        foreach ($this->classesToSkip as $classToSkip) {
            if (\strpos($classToSkip, '*') !== \false) {
                if (\fnmatch($classToSkip, $classLikeName, \FNM_NOESCAPE)) {
                    return \true;
                }
                continue;
            }
            if ($this->nodeNameResolver->isStringName($classLikeName, $classToSkip)) {
                return \true;
            }
        }
        return \false;
    }
    private function decorateClassConst(ClassConst $classConst) : void
    {
        $this->traverseNodesWithCallable($classConst->consts, static function (Node $subNode) {
            if ($subNode instanceof String_) {
                $subNode->setAttribute(self::IS_UNDER_CLASS_CONST, \true);
            }
            return null;
        });
    }
}
