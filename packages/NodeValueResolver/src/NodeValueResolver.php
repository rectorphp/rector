<?php declare(strict_types=1);

namespace Rector\NodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\MagicConst\Class_;
use PhpParser\Node\Scalar\MagicConst\Method;
use PhpParser\Node\Scalar\MagicConst\Namespace_;
use PhpParser\Node\Scalar\String_;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;
use Roave\BetterReflection\NodeCompiler\Exception\UnableToCompileNode;

/**
 * Inspired by https://github.com/Roave/BetterReflection/blob/master/test/unit/NodeCompiler/CompileNodeToValueTest.php
 */
final class NodeValueResolver
{
    /**
     * @var PerNodeValueResolverInterface[]
     */
    private $perNodeValueResolvers = [];

    public function addPerNodeValueResolver(PerNodeValueResolverInterface $perNodeValueResolver): void
    {
        $this->perNodeValueResolvers[] = $perNodeValueResolver;
    }

    /**
     * @return mixed
     */
    public function resolve(Node $node)
    {
        foreach ($this->perNodeValueResolvers as $perNodeValueResolver) {
            if (! is_a($node, $perNodeValueResolver->getNodeClass(), true)) {
                continue;
            }

            return $perNodeValueResolver->resolve($node);
        }

        if ($node instanceof String_ || $node instanceof DNumber || $node instanceof LNumber) {
            return $node->value;
        }

        if ($node instanceof Array_) {
            return $this->compileArray($node);
        }

        if ($node instanceof ConstFetch) {
            return $this->compileConstFetch($node);
        }

        if ($node instanceof Concat) {
            return $this->resolve($node->left) . $this->resolve($node->right);
        }

        if ($node instanceof Class_) {
            return $node->getAttribute(Attribute::CLASS_NAME);
        }

        if ($node instanceof Method) {
            $classMethodNode = $node->getAttribute(Attribute::SCOPE_NODE);

            return $node->getAttribute(Attribute::CLASS_NAME) . '::' . $classMethodNode->name->name;
        }

        if ($node instanceof Namespace_) {
            return (string) $node->getAttribute(Attribute::NAMESPACE);
        }

        throw new NotImplementedException(sprintf(
            '%s() was unable to resolve "%s" Node. Add new value resolver via addValueResolver() method.',
            __METHOD__,
            get_class($node)
        ));
    }

    /**
     * @return bool|mixed|null
     */
    private function compileConstFetch(ConstFetch $constNode)
    {
        $firstName = reset($constNode->name->parts);

        switch ($firstName) {
            case 'null':
                return null;
            case 'false':
                return false;
            case 'true':
                return true;
            default:
                if (! defined($firstName)) {
                    throw new UnableToCompileNode(
                        sprintf('Constant "%s" has not been defined', $firstName)
                    );
                }
                return constant($firstName);
        }
    }

    /**
     * @return mixed[]
     */
    private function compileArray(Array_ $arrayNode): array
    {
        $compiledArray = [];
        foreach ($arrayNode->items as $arrayItem) {
            $compiledValue = $this->resolve($arrayItem->value);
            if ($arrayItem->key === null) {
                $compiledArray[] = $compiledValue;

                continue;
            }

            $compiledArray[$this->resolve($arrayItem->key)] = $compiledValue;
        }

        return $compiledArray;
    }
}
