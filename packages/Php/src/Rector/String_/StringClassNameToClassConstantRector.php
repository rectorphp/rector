<?php declare(strict_types=1);

namespace Rector\Php\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Util\RectorStrings;
use ReflectionClass;

/**
 * @see https://wiki.php.net/rfc/class_name_scalars
 * @see \Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\StringClassNameToClassConstantRectorTest
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
        'Error', // can be string
    ])
    {
        $this->classesToSkip = $classesToSkip;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace string class names by <class>::class constant', [
            new CodeSample(
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
        $classLikeName = $node->value;

        // remove leading slash
        $classLikeName = ltrim($classLikeName, '\\');
        if ($classLikeName === '') {
            return null;
        }

        if (! $this->classLikeSensitiveExists($classLikeName)) {
            return null;
        }

        if (RectorStrings::isInArrayInsensitive($classLikeName, $this->classesToSkip)) {
            return null;
        }

        return new ClassConstFetch(new FullyQualified($classLikeName), 'class');
    }

    private function classLikeSensitiveExists(string $classLikeName): bool
    {
        if (! $this->classLikeInsensitiveExists($classLikeName)) {
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

    private function classLikeInsensitiveExists(string $classLikeName): bool
    {
        if (class_exists($classLikeName)) {
            return true;
        }

        if (interface_exists($classLikeName)) {
            return true;
        }

        return trait_exists($classLikeName);
    }
}
