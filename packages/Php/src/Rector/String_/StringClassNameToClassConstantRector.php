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

/**
 * @see https://wiki.php.net/rfc/class_name_scalars
 */
final class StringClassNameToClassConstantRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $classesToSkip = [];

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
        if (! $this->classLikeExists($classLikeName)) {
            return null;
        }

        if (RectorStrings::isInArrayInsensitive($classLikeName, $this->classesToSkip)) {
            return null;
        }

        return new ClassConstFetch(new FullyQualified($classLikeName), 'class');
    }

    private function classLikeExists(string $classLikeName): bool
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
