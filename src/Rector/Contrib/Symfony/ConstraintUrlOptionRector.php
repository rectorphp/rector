<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractRector;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#validator
 *
 * Before:
 * $containt = new Url(['checkDNS' => true]);
 *
 * After:
 * $containt = new Url(['checkDNS' => Url::CHECK_DNS_TYPE_ANY]);
 */
final class ConstraintUrlOptionRector extends AbstractRector
{
    /**
     * @todo complete FQN
     * @var string
     */
    private const URL_CONSTRAINT_CLASS = 'Url';

    public function getSetName(): string
    {
        return SetNames::SYMFONY;
    }

    public function sinceVersion(): float
    {
        return 4.0;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof ConstFetch) {
            return false;
        }

        if ($node->name->toString() !== 'true') {
            return false;
        }

        $prevNode = $node->getAttribute('prev');
        if (! $prevNode instanceof String_) {
            return false;
        }

        return $prevNode->value === 'checkDNS';
    }

    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        $classNameNode = new Name(self::URL_CONSTRAINT_CLASS);

        return new ClassConstFetch($classNameNode, 'CHECK_DNS_TYPE_ANY');
    }
}
