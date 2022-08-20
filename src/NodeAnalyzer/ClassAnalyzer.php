<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Util\StringUtils;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ClassAnalyzer
{
    /**
     * @var string
     * @see https://regex101.com/r/FQH6RT/1
     */
    private const ANONYMOUS_CLASS_REGEX = '#AnonymousClass\\w+$#';
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isAnonymousClassName(string $className) : bool
    {
        return StringUtils::isMatch($className, self::ANONYMOUS_CLASS_REGEX);
    }
    public function isAnonymousClass(Node $node) : bool
    {
        if (!$node instanceof Class_) {
            return \false;
        }
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof New_) {
            return \false;
        }
        if ($node->isAnonymous()) {
            return \true;
        }
        $className = $this->nodeNameResolver->getName($node);
        if ($className === null) {
            return \true;
        }
        // match PHPStan pattern for anonymous classes
        return StringUtils::isMatch($className, self::ANONYMOUS_CLASS_REGEX);
    }
}
