<?php

declare(strict_types=1);

namespace Rector\Php55\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Scalar\MagicConst\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/deprecations_php_7_4 (not confirmed yet)
 * @see https://3v4l.org/INd7o
 * @see \Rector\Php55\Tests\Rector\Class_\ClassConstantToSelfClassRector\ClassConstantToSelfClassRectorTest
 */
final class ClassConstantToSelfClassRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `__CLASS__` to self::class', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
   public function callOnMe()
   {
       var_dump(__CLASS__);
   }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
   public function callOnMe()
   {
       var_dump(self::class);
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

        return $this->nodeFactory->createSelfFetchConstant('class', $node);
    }
}
