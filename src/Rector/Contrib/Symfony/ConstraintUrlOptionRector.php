<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\NodeFactory\NodeFactory;
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
     * @var string
     */
    private const URL_CONSTRAINT_CLASS = 'Symfony\Component\Validator\Constraints\Url';

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof ConstFetch) {
            return false;
        }

        if ($node->name->toString() !== 'true') {
            return false;
        }

        $prevNode = $node->getAttribute(Attribute::PREVIOUS_NODE);

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
        return $this->nodeFactory->createClassConstant(
            self::URL_CONSTRAINT_CLASS,
            'CHECK_DNS_TYPE_ANY'
        );
    }
}
