<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/deprecations_php_7_4 (not confirmed yet)
 * @see https://3v4l.org/dJgXd
 * @see \Rector\Php74\Tests\Rector\FuncCall\GetCalledClassToStaticClassRector\GetCalledClassToStaticClassRectorTest
 */
final class GetCalledClassToStaticClassRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change get_called_class() to static::class', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
   public function callOnMe()
   {
       var_dump(get_called_class());
   }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
   public function callOnMe()
   {
       var_dump(static::class);
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::CLASSNAME_CONSTANT)) {
            return null;
        }

        if (! $this->isName($node, 'get_called_class')) {
            return null;
        }

        return $this->nodeFactory->createClassConstFetch('static', 'class');
    }
}
