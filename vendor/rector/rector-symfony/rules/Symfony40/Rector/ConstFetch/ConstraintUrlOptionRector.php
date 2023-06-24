<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony40\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\New_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#validator
 *
 * @see \Rector\Symfony\Tests\Symfony40\Rector\ConstFetch\ConstraintUrlOptionRector\ConstraintUrlOptionRectorTest
 */
final class ConstraintUrlOptionRector extends AbstractRector
{
    /**
     * @var string
     */
    private const URL_CONSTRAINT_CLASS = 'Symfony\\Component\\Validator\\Constraints\\Url';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.', [new CodeSample('$constraint = new Url(["checkDNS" => true]);', '$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node) : ?New_
    {
        if (!$this->isObjectType($node, new ObjectType('Symfony\\Component\\Validator\\Constraints\\Url'))) {
            return null;
        }
        foreach ($node->getArgs() as $arg) {
            if (!$arg->value instanceof Array_) {
                continue;
            }
            foreach ($arg->value->items as $arrayItem) {
                if (!$arrayItem instanceof ArrayItem) {
                    continue;
                }
                if (!$this->isCheckDNSKey($arrayItem)) {
                    continue;
                }
                if (!$this->valueResolver->isTrue($arrayItem->value)) {
                    return null;
                }
                $arrayItem->value = $this->nodeFactory->createClassConstFetch(self::URL_CONSTRAINT_CLASS, 'CHECK_DNS_TYPE_ANY');
                return $node;
            }
        }
        return null;
    }
    private function isCheckDNSKey(ArrayItem $arrayItem) : bool
    {
        if (!$arrayItem->key instanceof Expr) {
            return \false;
        }
        return $this->valueResolver->isValue($arrayItem->key, 'checkDNS');
    }
}
