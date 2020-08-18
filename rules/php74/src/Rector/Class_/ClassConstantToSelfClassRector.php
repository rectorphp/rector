<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;

/**
 * @see https://wiki.php.net/rfc/deprecations_php_7_4 (not confirmed yet)
 * @see https://3v4l.org/INd7o
 * @see \Rector\Php74\Tests\Rector\Class_\ClassConstantToSelfClassRector\ClassConstantToSelfClassRectorTest
 */
final class ClassConstantToSelfClassRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change `__CLASS__` to self::class', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
   public function callOnMe()
   {
       var_dump(__CLASS__);
   }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
   public function callOnMe()
   {
       var_dump(self::class);
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::CLASSNAME_CONSTANT)) {
            return null;
        }

        return new ClassConstFetch(new Name('self'), 'class');
    }
}
