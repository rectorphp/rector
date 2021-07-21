<?php

declare(strict_types=1);

namespace Rector\Php55\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StaticRectorStrings;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/class_name_scalars https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 *
 * @see \Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\StringClassNameToClassConstantRectorTest
 */
final class StringClassNameToClassConstantRector extends AbstractRector implements ConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @api
     * @var string
     */
    public const CLASSES_TO_SKIP = 'classes_to_skip';

    /**
     * @var string[]
     */
    private array $classesToSkip = [
        // can be string
        'Error',
        'Exception',
    ];

    public function __construct(
        private ClassLikeExistenceChecker $classLikeExistenceChecker
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace string class names by <class>::class constant', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
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
                ,
                <<<'CODE_SAMPLE'
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
,
                [
                    self::CLASSES_TO_SKIP => ['ClassName', 'AnotherClassName'],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    /**
     * @param String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLikeName = $node->value;

        // remove leading slash
        $classLikeName = ltrim($classLikeName, '\\');
        if ($classLikeName === '') {
            return null;
        }

        if ($this->shouldSkip($classLikeName, $node)) {
            return null;
        }

        $fullyQualified = new FullyQualified($classLikeName);
        return new ClassConstFetch($fullyQualified, 'class');
    }

    /**
     * @param array<string, string[]> $configuration
     */
    public function configure(array $configuration): void
    {
        if (! isset($configuration[self::CLASSES_TO_SKIP])) {
            return;
        }

        $this->classesToSkip = $configuration[self::CLASSES_TO_SKIP];
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::CLASSNAME_CONSTANT;
    }

    private function isPartOfIsAFuncCall(String_ $string): bool
    {
        $parent = $string->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Arg) {
            return false;
        }

        $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParent instanceof FuncCall) {
            return false;
        }

        return $this->nodeNameResolver->isName($parentParent, 'is_a');
    }

    private function shouldSkip(string $classLikeName, String_ $string): bool
    {
        if (! $this->classLikeExistenceChecker->doesClassLikeInsensitiveExists($classLikeName)) {
            return true;
        }

        if (StaticRectorStrings::isInArrayInsensitive($classLikeName, $this->classesToSkip)) {
            return true;
        }

        if ($this->isPartOfIsAFuncCall($string)) {
            return true;
        }

        // allow class strings to be part of class const arrays, as probably on purpose
        $parentClassConst = $this->betterNodeFinder->findParentType($string, ClassConst::class);
        return $parentClassConst instanceof ClassConst;
    }
}
