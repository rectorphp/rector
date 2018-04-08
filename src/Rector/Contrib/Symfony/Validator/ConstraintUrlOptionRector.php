<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Validator;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#validator
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns true value to Url::CHECK_DNS_TYPE_ANY in Validator in Symfony.', [
            new CodeSample(
                '$constraint = new Url(["checkDNS" => true]);',
                '$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);'
            ),
        ]);
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
        return $this->nodeFactory->createClassConstant(self::URL_CONSTRAINT_CLASS, 'CHECK_DNS_TYPE_ANY');
    }
}
