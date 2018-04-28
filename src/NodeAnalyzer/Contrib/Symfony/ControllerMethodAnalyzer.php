<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer\Contrib\Symfony;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;

/**
 * @todo Move to /Bridge, to make clear separation from Rector core
 */
final class ControllerMethodAnalyzer
{
    /**
     * @var string[]
     */
    private $supportedClasses = [
        'Symfony\Bundle\FrameworkBundle\Controller\Controller',
        'Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
    ];

    /**
     * Detect if is <some>Action() in Controller
     */
    public function isAction(Node $node): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);

        if (! in_array($parentClassName, $this->supportedClasses, true)) {
            return false;
        }

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;

        return Strings::endsWith($identifierNode->toString(), 'Action');
    }
}
