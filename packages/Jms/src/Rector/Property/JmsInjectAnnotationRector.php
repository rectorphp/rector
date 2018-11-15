<?php declare(strict_types=1);

namespace Rector\Jms\Rector\Property;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Application\ErrorCollector;
use Rector\Bridge\Contract\AnalyzedApplicationContainerInterface;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use function Safe\sprintf;

/**
 * @see https://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations#inject
 */
final class JmsInjectAnnotationRector extends AbstractRector
{
    /**
     * @var string
     */
    private const INJECT_ANNOTATION = 'JMS\DiExtraBundle\Annotation\Inject';

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var AnalyzedApplicationContainerInterface
     */
    private $analyzedApplicationContainer;

    /**
     * @var ErrorCollector
     */
    private $errorCollector;

    public function __construct(
        DocBlockAnalyzer $docBlockAnalyzer,
        AnalyzedApplicationContainerInterface $analyzedApplicationContainer,
        ErrorCollector $errorCollector
    ) {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->analyzedApplicationContainer = $analyzedApplicationContainer;
        $this->errorCollector = $errorCollector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes properties with `@JMS\DiExtraBundle\Annotation\Inject` to constructor injection',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
    /**
     * @DI\Inject("entity.manager")
     */
    private $entityManager;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = entityManager;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->docBlockAnalyzer->hasTag($node, self::INJECT_ANNOTATION)) {
            return null;
        }

        $type = $this->resolveType($node);
        if ($type === null) {
            return null;
        }

        if (! $this->docBlockAnalyzer->hasTag($node, 'var')) {
            $this->docBlockAnalyzer->addVarTag($node, $type);
        }

        $this->docBlockAnalyzer->removeTagFromNode($node, self::INJECT_ANNOTATION);

        // set to private
        $node->flags = Class_::MODIFIER_PRIVATE;

        $this->addPropertyToClass($node->getAttribute(Attribute::CLASS_NODE), $type, $this->getName($node));

        return $node;
    }

    private function resolveType(Node $node): ?string
    {
        $injectTagNode = $this->docBlockAnalyzer->getTagByName($node, self::INJECT_ANNOTATION);

        $serviceName = $this->resolveServiceName($injectTagNode, $node);
        if ($serviceName) {
            if ($this->analyzedApplicationContainer->hasService($serviceName)) {
                return $this->analyzedApplicationContainer->getTypeForName($serviceName);
            }

            // collect error
            $this->errorCollector->addErrorWithRectorMessage(
                self::class,
                sprintf('Service "%s" was not found in DI Container of your Symfony App.', $serviceName)
            );
        }

        $varTypes = $this->docBlockAnalyzer->getVarTypes($node);
        if (count($varTypes)) {
            return array_shift($varTypes);
        }

        return null;
    }

    private function resolveServiceName(PhpDocTagNode $phpDocTagNode, Node $node): ?string
    {
        $injectTagContent = (string) $phpDocTagNode->value;
        $match = Strings::match($injectTagContent, '#(\'|")(?<serviceName>.*?)(\'|")#');

        if ($match['serviceName']) {
            return $match['serviceName'];
        }

        $match = Strings::match($injectTagContent, '#(\'|")%(?<parameterName>.*?)%(\'|")#');
        // it's parameter, we don't resolve that here
        if (isset($match['parameterName'])) {
            return null;
        }

        return $this->getName($node);
    }
}
