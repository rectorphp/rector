<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Exception\NotImplementedException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\ZendToSymfony\ValueObject\ZendClass;

/**
 * 1)
 * $this->_helper->onlinePayment()->isPaid()
 * ↓
 * $this->Helper_OnlinePayment->direct()->isPaid()
 *
 * 2)
 * $this->_helper->viewRenderer->setViewScriptPathSpec()
 * ↓
 * $this->Helper_ViewRenderer->setViewScriptPathSpec()
 *
 * 3)
 * $this->_helper->json([])
 * ↓
 * $this->Helper_Json->direct([])
 *
 * @sponsor Thanks https://previo.cz/ for sponsoring this rule
 *
 * @see \Rector\ZendToSymfony\Tests\Rector\MethodCall\ThisHelperToServiceMethodCallRector\ThisHelperToServiceMethodCallRectorTest
 */
final class ThisHelperToServiceMethodCallRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change magic $this->_helper->calls() to constructor injection of helper services',
            [
                new CodeSample(
                    <<<'PHP'
class SomeController
{
    /**
     * @var Zend_Controller_Action_HelperBroker
     */
    private $_helper;

    public function someAction()
    {
        $this->_helper->onlinePayment(1000);
        
        $this->_helper->onlinePayment()->isPaid();
    }
}
PHP
                    ,
                    <<<'PHP'
class SomeController
{
    /**
     * @var Zend_Controller_Action_HelperBroker
     */
    private $_helper;
    
    /**
     * @var Zend_Controller_Action_Helper_OnlinePayment
     */
    private $onlinePaymentHelper;

    public function __construct(Zend_Controller_Action_Helper_OnlinePayment $onlinePaymentHelper)
    {
        $this->onlinePaymentHelper = onlinePaymentHelper;
    }

    public function someAction()
    {
        $this->onlinePaymentHelper->direct(1000);

        $this->onlinePaymentHelper->direct()->isPaid();
    }
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof MethodCall) {
            return null;
        }

        if (! $node->var instanceof PropertyFetch) {
            return null;
        }

        if (! $this->isObjectType($node->var, ZendClass::HELPER_BROKER)) {
            return null;
        }

        $helperName = $this->resolveHelperName($node);
        $helperClass = $this->resolveHelperClassType($helperName);

        $propertyName = $helperName . 'Helper';

        /** @var Class_ $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        $this->addPropertyToClass($class, $helperClass, $propertyName);

        $propertyFetch = $this->createPropertyFetch('this', $propertyName);

        return new MethodCall($propertyFetch, 'direct', $node->args);
    }

    private function resolveHelperClassType(string $helperName): Type
    {
        // @todo make configurable/adaptable for custom helper override in own namespace
        $possibleClasses = [sprintf('Zend_Controller_Action_Helper_%s', ucfirst($helperName))];

        foreach ($possibleClasses as $possibleClass) {
            if (class_exists($possibleClass)) {
                return new ObjectType($possibleClass);
            }
        }

        throw new NotImplementedException(sprintf(
            'Class for "%s" filter was not found. Try finding the class location and add it to composer > autoload-dev > classmap',
            $helperName
        ));
    }

    private function resolveHelperName(MethodCall $methodCall): string
    {
        /** @var string $methodName */
        $methodName = $this->getName($methodCall->name);

        // special case handled by another path
        if ($methodName === 'getHelper') {
            return $this->getValue($methodCall->args[0]->value);
        }

        return $methodName;
    }
}
