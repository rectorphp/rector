<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Utils;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Builder\MethodBuilder;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Catches @method annotations at childs of Nette\Object
 * and converts them to real methods
 *
 * Covers @see https://github.com/RectorPHP/Rector/issues/49
 *
 * This would be nice test: https://github.com/Kdyby/Redis/blob/cbe29eef207ded876f41a40281e1fa746b3a2330/src/Kdyby/Redis/RedisClient.php#L29
 */
final class MagicMethodRector extends AbstractRector
{
    /**
     * @var string
     */
    private const MAGIC_METHODS_PATTERN = '~^
            [ \t*]*  @method  [ \t]+
            (?: [^\s(]+  [ \t]+ )?
            (set|get|is|add)  ([A-Z]\w*)  [ \t]*
            (?: \(  [ \t]* ([^)$\s]+)  )?
        ()~mx';

    /**
     * @var mixed[]
     */
    private $magicMethods = [];

    /**
     * @var MethodBuilder
     */
    private $methodBuilder;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    public function __construct(MethodBuilder $methodBuilder, DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->methodBuilder = $methodBuilder;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $this->magicMethods = [];

        if (! $node instanceof Class_) {
            return false;
        }

        if (! $this->isNetteObjectChild($node)) {
            return false;
        }

        $docComments = $node->getAttribute('comments');
        if ($docComments === null) {
            return false;
        }

        /** @var Doc $docComment */
        $docComment = $docComments[0];

        // @todo consider NamespaceResolver NodeTraverser
        $currentNamespace = $node->namespacedName->slice(0, -1)
            ->toString();

        $this->magicMethods = $this->matchMagicMethodsInDocComment($currentNamespace, $docComment->getText());

        return (bool) count($this->magicMethods);
    }

    /**
     * @param Class_ $classNode
     */
    public function refactor(Node $classNode): ?Node
    {
        foreach ($this->magicMethods as $methodName => $methodSettings) {
            $this->methodBuilder->addMethodToClass(
                $classNode,
                $methodName,
                $methodSettings['propertyType'],
                $methodSettings['propertyName']
            );

            $this->docBlockAnalyzer->removeAnnotationFromNode($classNode, 'method', $methodName);
        }

        return $classNode;
    }

    private function isNetteObjectChild(Class_ $classNode): bool
    {
        if ($classNode->extends === null) {
            return false;
        }

        $parentClassName = (string) $classNode->extends->getAttribute(Attribute::RESOLVED_NAME);

        return $parentClassName === 'Nette\Object';
    }

    /**
     * Mimics https://github.com/nette/utils/blob/v2.3/src/Utils/ObjectMixin.php#L285
     * only without reflection.
     *
     * @return mixed[]
     */
    private function matchMagicMethodsInDocComment(string $currentNamespace, string $text): array
    {
        preg_match_all(self::MAGIC_METHODS_PATTERN, $text, $matches, PREG_SET_ORDER);

        $methods = [];

        foreach ($matches as $match) {
            [, $op, $prop, $type] = $match;

            $name = $op . $prop;
            $prop = strtolower($prop[0]) . substr($prop, 1) . ($op === 'add' ? 's' : '');
            // @todo: file aware BetterReflection? - FileLocator
            // use CurrentFileProvider? load in ProcessCommand, enable here
//            if ($rc->hasProperty($prop) && ($rp = $rc->getProperty($prop)) && !$rp->isStatic()) {
//                $rp->setAccessible(TRUE);
            if ($op === 'get' || $op === 'is') {
                $type = null;
                $op = 'get';
            } elseif (! $type
                && preg_match('#@var[ \t]+(\S+)' . ($op === 'add' ? '\[\]#' : '#'), $rp->getDocComment(), $match)
            ) {
                $type = $match[1];
            }

            if ($type && $currentNamespace && preg_match('#^[A-Z]\w+(\[|\||\z)#', $type)) {
                $type = $currentNamespace . '\\' . $type;
            }

            $methods[$name] = [
                'propertyType' => $type,
                'propertyName' => $prop,
            ];
        }

        return $methods;
    }
}
