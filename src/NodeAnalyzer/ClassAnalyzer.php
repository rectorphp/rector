<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
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
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isAnonymousClass(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Expr\New_) {
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
        return (bool) \RectorPrefix20211020\Nette\Utils\Strings::match($className, self::ANONYMOUS_CLASS_REGEX);
    }
}
