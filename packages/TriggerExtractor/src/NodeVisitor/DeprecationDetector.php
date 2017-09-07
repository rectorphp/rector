<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;
use Rector\TriggerExtractor\Deprecation\DeprecationCollector;
use Rector\TriggerExtractor\Deprecation\DeprecationFactory;

final class DeprecationDetector extends NodeVisitorAbstract
{
    /**
     * @var DeprecationCollector
     */
    private $deprecationCollector;

    /**
     * @var DeprecationFactory
     */
    private $deprecationFactory;

    public function __construct(
        DeprecationCollector $deprecationCollector,
        DeprecationFactory $triggerMessageResolver
    ) {
        $this->deprecationCollector = $deprecationCollector;
        $this->deprecationFactory = $triggerMessageResolver;
    }

    public function enterNode(Node $node): void
    {
        // @see https://github.com/sensiolabs-de/deprecation-detector/blob/master/src/Visitor/Deprecation/FindDeprecatedTagsVisitor.php
//        if (! $this->isTriggerErrorUserDeprecated($node)) {
//            return;
//        }

        if (! $this->hasTriggerErrorUserDeprecatedInside($node)) {
            return;
        }


        if (! $this->hasDeprecatedDocComment($node)) {
            return;
        }

        $scope = $node->getAttribute(Attribute::SCOPE);

        /** @var FuncCall $node */
        $deprecation = $this->deprecationFactory->createFromNode($node->args[0]->value, $scope);

        $this->deprecationCollector->addDeprecation($deprecation);
    }

    /**
     * This detects: "trigger_error(<some-content>, E_USER_DEPREDCATED)";
     */
    private function isTriggerErrorUserDeprecated(Node $node): bool
    {
        if (! $this->isFunctionWithName($node, 'trigger_error')) {
            return false;
        }

        /** @var FuncCall $node */
        if (count($node->args) !== 2) {
            return false;
        }

        /** @var Arg $secondArgumentNode */
        $secondArgumentNode = $node->args[1];
        if (! $secondArgumentNode->value instanceof ConstFetch) {
            return false;
        }

        /** @var ConstFetch $constFetchNode */
        $constFetchNode = $secondArgumentNode->value;

        return $constFetchNode->name->toString() === 'E_USER_DEPRECATED';
    }

    private function isFunctionWithName(Node $node, string $name): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        if (! $node->name instanceof Name) {
            return false;
        }

        return $node->name->toString() === $name;
    }

    /**
     * @todo extract to docblock analyzer
     * use deprecated annotation check
     * @param Node $node
     *
     * @return bool
     */
    protected function hasDeprecatedDocComment(Node $node)
    {
        try {
            $docBlock = new DocBlock((string) $node->getDocComment());
            return count($docBlock->getTagsByName('deprecated')) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
