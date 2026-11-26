<?php

declare (strict_types=1);
namespace Rector\Php55\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Enum\ObjectReference;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix202611\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\StringClassNameToClassConstantRectorTest
 */
final class StringClassNameToClassConstantRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @var string[]
     */
    private array $classesToSkip = [];
    public function __construct(ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace string class names by `<class>::class` constant', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, ['ClassName', 'AnotherClassName']), new CodeSample(<<<'CODE_SAMPLE'
namespace App;

class SomeController
{
    public function run()
    {
        return 'App\SomeController::indexAction';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
namespace App;

class SomeController
{
    public function run()
    {
        return self::class . '::indexAction';
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
        return [String_::class, Concat::class];
    }
    /**
     * @param String_|Concat $node
     * @return \PhpParser\Node\Expr\BinaryOp\Concat|\PhpParser\Node\Expr\ClassConstFetch|null
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Concat) {
            return $this->refactorMethodPrefix($node);
        }
        if ($this->shouldSkipIsA($node)) {
            return null;
        }
        if (strpos($node->value, '::') !== \false) {
            return $this->refactorMethodReference($node);
        }
        $classLikeName = $node->value;
        // remove leading slash
        $classLikeName = ltrim($classLikeName, '\\');
        if ($classLikeName === '') {
            return null;
        }
        if ($this->shouldSkip($classLikeName)) {
            return null;
        }
        if ($this->isSelfClassName($node, $classLikeName)) {
            return new ClassConstFetch(new Name(ObjectReference::SELF), 'class');
        }
        $fullyQualified = new FullyQualified($classLikeName);
        return new ClassConstFetch($fullyQualified, 'class');
    }
    /**
     * @param string[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allString($configuration);
        $this->classesToSkip = $configuration;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_55;
    }
    private function shouldSkip(string $classLikeName): bool
    {
        // skip short class names, mostly invalid use of strings
        if (strpos($classLikeName, '\\') === \false) {
            return \true;
        }
        // possibly string
        if (ctype_lower($classLikeName[0])) {
            return \true;
        }
        if (!$this->reflectionProvider->hasClass($classLikeName)) {
            return \true;
        }
        foreach ($this->classesToSkip as $classToSkip) {
            if (strpos($classToSkip, '*') !== \false) {
                if (fnmatch($classToSkip, $classLikeName, \FNM_NOESCAPE)) {
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
    /**
     * 'App\SomeClass::run' => \App\SomeClass::class . '::run', or self::class . '::run' inside App\SomeClass
     */
    private function refactorMethodReference(String_ $string): ?Concat
    {
        $parts = explode('::', ltrim($string->value, '\\'));
        if (count($parts) !== 2) {
            return null;
        }
        [$className, $methodName] = $parts;
        if (preg_match('#^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$#', $methodName) !== 1) {
            return null;
        }
        return $this->createClassConcat($string, $className, '::' . $methodName);
    }
    /**
     * 'App\SomeClass::' . $method => \App\SomeClass::class . '::' . $method, or self::class inside App\SomeClass
     */
    private function refactorMethodPrefix(Concat $concat): ?Concat
    {
        if (!$concat->left instanceof String_) {
            return null;
        }
        $value = ltrim($concat->left->value, '\\');
        if (substr_compare($value, '::', -strlen('::')) !== 0 || substr_count($value, '::') !== 1) {
            return null;
        }
        $classConcat = $this->createClassConcat($concat->left, (string) substr($value, 0, -2), '::');
        if (!$classConcat instanceof Concat) {
            return null;
        }
        $concat->left = $classConcat;
        return $concat;
    }
    private function isSelfClassName(String_ $string, string $className): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($string);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        // self::class inside a trait is the using class, not the trait
        if ($classReflection->isAnonymous() || $classReflection->isTrait()) {
            return \false;
        }
        return $classReflection->getName() === $className;
    }
    private function createClassConcat(String_ $string, string $className, string $suffix): ?Concat
    {
        if ($this->shouldSkip($className)) {
            return null;
        }
        $class = $this->isSelfClassName($string, $className) ? new Name(ObjectReference::SELF) : new FullyQualified($className);
        return new Concat(new ClassConstFetch($class, 'class'), new String_($suffix));
    }
    private function shouldSkipIsA(String_ $string): bool
    {
        if (!$string->getAttribute(AttributeKey::IS_ARG_VALUE, \false)) {
            return \false;
        }
        $funcCallName = $string->getAttribute(AttributeKey::FROM_FUNC_CALL_NAME);
        return $funcCallName === 'is_a';
    }
}
