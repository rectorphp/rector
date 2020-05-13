<?php

declare(strict_types=1);

namespace Rector\Php55\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;

/**
 * @see https://wiki.php.net/rfc/class_name_scalars
 *
 * @see \Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\StringClassNameToClassConstantRectorTest
 */
final class StringClassNameToClassConstantRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $classesToSkip = [];

    /**
     * @var string[]
     */
    private $sensitiveExistingClasses = [];

    /**
     * @var string[]
     */
    private $sensitiveNonExistingClasses = [];

    /**
     * @param string[] $classesToSkip
     */
    public function __construct(array $classesToSkip = [
        // can be string
        'Error',
    ])
    {
        $this->classesToSkip = $classesToSkip;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace string class names by <class>::class constant', [
            new CodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
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

        $name = new FullyQualified($classLikeName);
        /** @see \Rector\PostRector\Collector\UseNodesToAddCollector::isShortImported() */
        $name->setAttribute(AttributeKey::FILE_INFO, $node->getAttribute(AttributeKey::FILE_INFO));

        return new ClassConstFetch($name, 'class');
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

        $classReflection = new ReflectionClass($classLikeName);

        if ($classLikeName !== $classReflection->getName()) {
            $this->sensitiveNonExistingClasses[] = $classLikeName;
            return false;
        }

        $this->sensitiveExistingClasses[] = $classLikeName;
        return true;
    }
}
