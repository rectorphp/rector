<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\General;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220606\TYPO3\CMS\Core\Utility\GeneralUtility;
use RectorPrefix20220606\Webmozart\Assert\Assert;
/**
 * @see \Ssch\TYPO3Rector\Tests\Rector\General\MethodGetInstanceToMakeInstanceCallRector\MethodGetInstanceToMakeInstanceCallRectorTest
 */
final class MethodGetInstanceToMakeInstanceCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASSES_GET_INSTANCE_TO_MAKE_INSTANCE = 'classes-get-instance-to-make-instance';
    /**
     * @var array<string, string[]>
     */
    private const EXAMPLE_CONFIGURATION = [self::CLASSES_GET_INSTANCE_TO_MAKE_INSTANCE => ['SomeClass']];
    /**
     * @var string[]
     */
    private $classes = [];
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $className = $this->nodeNameResolver->getName($node->class);
        if (null === $className) {
            return null;
        }
        $class = $this->nodeFactory->createClassConstReference($className);
        return $this->nodeFactory->createStaticCall(GeneralUtility::class, 'makeInstance', [$class]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use GeneralUtility::makeInstance instead of getInstance call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$instance = TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Resource\Index\ExtractorRegistry;

$instance = GeneralUtility::makeInstance(ExtractorRegistry::class);
CODE_SAMPLE
, self::EXAMPLE_CONFIGURATION)]);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $classes = $configuration[self::CLASSES_GET_INSTANCE_TO_MAKE_INSTANCE] ?? $configuration;
        Assert::isArray($classes);
        Assert::allString($classes);
        $this->classes = $classes;
    }
    private function shouldSkip(StaticCall $staticCall) : bool
    {
        if ([] === $this->classes) {
            return \true;
        }
        if (!$this->isName($staticCall->name, 'getInstance')) {
            return \true;
        }
        foreach ($this->classes as $class) {
            if ($this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($staticCall, new ObjectType($class))) {
                return \false;
            }
        }
        return \true;
    }
}
