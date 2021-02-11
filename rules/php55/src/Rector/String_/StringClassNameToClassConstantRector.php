<?php

declare(strict_types=1);

namespace Rector\Php55\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StaticRectorStrings;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/class_name_scalars
 * @see https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 *
 * @see \Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\StringClassNameToClassConstantRectorTest
 */
final class StringClassNameToClassConstantRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const CLASSES_TO_SKIP = '$classesToSkip';

    /**
     * @var string[]
     */
    private $classesToSkip = [
        // can be string
        'Error',
        'Exception',
    ];

    /**
     * @var string[]
     */
    private $sensitiveExistingClasses = [];

    /**
     * @var string[]
     */
    private $sensitiveNonExistingClasses = [];

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
     * @return string[]
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
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::CLASSNAME_CONSTANT)) {
            return null;
        }

        $classLikeName = $node->value;

        // remove leading slash
        $classLikeName = ltrim($classLikeName, '\\');
        if ($classLikeName === '') {
            return null;
        }

        if (! $this->classLikeSensitiveExists($classLikeName)) {
            return null;
        }

        if (StaticRectorStrings::isInArrayInsensitive($classLikeName, $this->classesToSkip)) {
            return null;
        }

        if ($this->isPartOfIsAFuncCall($node)) {
            return null;
        }

        $fullyQualified = new FullyQualified($classLikeName);

        /** @see \Rector\PostRector\Collector\UseNodesToAddCollector::isShortImported() */
        $fullyQualified->setAttribute(AttributeKey::FILE_INFO, $node->getAttribute(AttributeKey::FILE_INFO));

        return new ClassConstFetch($fullyQualified, 'class');
    }

    public function configure(array $configuration): void
    {
        if (isset($configuration[self::CLASSES_TO_SKIP])) {
            $this->classesToSkip = $configuration[self::CLASSES_TO_SKIP];
        }
    }

    private function classLikeSensitiveExists(string $classLikeName): bool
    {
        if (! ClassExistenceStaticHelper::doesClassLikeExist($classLikeName)) {
            return false;
        }

        // already known values
        if (in_array($classLikeName, $this->sensitiveExistingClasses, true)) {
            return true;
        }

        if (in_array($classLikeName, $this->sensitiveNonExistingClasses, true)) {
            return false;
        }

        $reflectionClass = new ReflectionClass($classLikeName);

        if ($classLikeName !== $reflectionClass->getName()) {
            $this->sensitiveNonExistingClasses[] = $classLikeName;
            return false;
        }

        $this->sensitiveExistingClasses[] = $classLikeName;
        return true;
    }

    private function isPartOfIsAFuncCall(String_ $string): bool
    {
        $parent = $string->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Arg) {
            return false;
        }
        $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParent instanceof Node) {
            return false;
        }

        return $this->isFuncCallName($parentParent, 'is_a');
    }
}
