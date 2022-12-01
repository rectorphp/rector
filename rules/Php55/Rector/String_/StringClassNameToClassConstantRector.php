<?php

declare (strict_types=1);
namespace Rector\Php55\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Naming\Naming\AliasNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202212\Webmozart\Assert\Assert;
/**
 * @changelog https://wiki.php.net/rfc/class_name_scalars https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 *
 * @see \Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\StringClassNameToClassConstantRectorTest
 */
final class StringClassNameToClassConstantRector extends AbstractRector implements AllowEmptyConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @var string[]
     */
    private $classesToSkip = [];
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\AliasNameResolver
     */
    private $aliasNameResolver;
    public function __construct(ReflectionProvider $reflectionProvider, AliasNameResolver $aliasNameResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->aliasNameResolver = $aliasNameResolver;
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
, ['ClassName', 'AnotherClassName'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $classLikeName = $node->value;
        // remove leading slash
        $classLikeName = \ltrim($classLikeName, '\\');
        if ($classLikeName === '') {
            return null;
        }
        if ($this->shouldSkip($classLikeName, $node)) {
            return null;
        }
        $fullyQualified = new FullyQualified($classLikeName);
        $name = clone $fullyQualified;
        $name->setAttribute(AttributeKey::PARENT_NODE, $node->getAttribute(AttributeKey::PARENT_NODE));
        $aliasName = $this->aliasNameResolver->resolveByName($name);
        $fullyQualifiedOrAliasName = \is_string($aliasName) ? new Name($aliasName) : $fullyQualified;
        if ($classLikeName !== $node->value) {
            $preSlashCount = \strlen($node->value) - \strlen($classLikeName);
            $preSlash = \str_repeat('\\', $preSlashCount);
            $string = new String_($preSlash);
            return new Concat($string, new ClassConstFetch($fullyQualifiedOrAliasName, 'class'));
        }
        return new ClassConstFetch($fullyQualifiedOrAliasName, 'class');
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString($configuration);
        $this->classesToSkip = $configuration;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::CLASSNAME_CONSTANT;
    }
    private function isPartOfIsAFuncCall(String_ $string) : bool
    {
        $parentNode = $string->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Arg) {
            return \false;
        }
        $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentParentNode instanceof FuncCall) {
            return \false;
        }
        return $this->nodeNameResolver->isName($parentParentNode, 'is_a');
    }
    private function shouldSkip(string $classLikeName, String_ $string) : bool
    {
        if (!$this->reflectionProvider->hasClass($classLikeName)) {
            return \true;
        }
        $classReflection = $this->reflectionProvider->getClass($classLikeName);
        if ($classReflection->getName() !== $classLikeName) {
            return \true;
        }
        // skip short class names, mostly invalid use of strings
        if (\strpos($classLikeName, '\\') === \false) {
            return \true;
        }
        // possibly string
        if (\ctype_lower($classLikeName[0])) {
            return \true;
        }
        foreach ($this->classesToSkip as $classToSkip) {
            if ($this->nodeNameResolver->isStringName($classLikeName, $classToSkip)) {
                return \true;
            }
        }
        if ($this->isPartOfIsAFuncCall($string)) {
            return \true;
        }
        // allow class strings to be part of class const arrays, as probably on purpose
        $parentClassConst = $this->betterNodeFinder->findParentType($string, ClassConst::class);
        return $parentClassConst instanceof ClassConst;
    }
}
