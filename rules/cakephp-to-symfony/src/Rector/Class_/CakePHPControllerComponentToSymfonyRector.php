<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\CakePHPToSymfony\Rector\AbstractCakePHPRector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/2/en/controllers/components.html
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerComponentToSymfonyRector\CakePHPControllerComponentToSymfonyRectorTest
 */
final class CakePHPControllerComponentToSymfonyRector extends AbstractCakePHPRector
{
    /**
     * @var string[]
     */
    private $componentsClasses = [];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP 2.4 Controller $components property to Symfony 5', [
            new CodeSample(
                <<<'PHP'
class MessagesController extends \AppController
{
    public $components = ['Overview'];

    public function someAction()
    {
        $this->Overview->filter();
    }
}

class OverviewComponent extends \Component
{
    public function filter()
    {
    }
}
PHP
,
                <<<'PHP'
class MessagesController extends \AppController
{
    private function __construct(OverviewComponent $overviewComponent)
    {
        $this->overviewComponent->filter();
    }

    public function someAction()
    {
        $this->overviewComponent->filter();
    }
}

class OverviewComponent extends \Component
{
    public function filter()
    {
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInCakePHPController($node)) {
            return null;
        }

        $componentsProperty = $node->getProperty('components');
        if ($componentsProperty === null) {
            return null;
        }

        $defaultValue = $componentsProperty->props[0]->default;
        if ($defaultValue === null) {
            return null;
        }

        $componentNames = $this->getValue($defaultValue);
        if (! is_array($componentNames)) {
            throw new ShouldNotHappenException();
        }

        $this->removeNode($componentsProperty);

        $componentClasses = $this->matchComponentClass($componentNames);

        $oldProperyNameToNewPropertyName = [];

        foreach ($componentClasses as $componentName => $componentClass) {
            $componentClassShortName = $this->getShortName($componentClass);
            $propertyShortName = lcfirst($componentClassShortName);
            $this->addPropertyToClass($node, new ObjectType($componentClass), $propertyShortName);

            $oldProperyNameToNewPropertyName[$componentName] = $propertyShortName;
        }

        $this->classManipulator->renamePropertyFetches($node, $oldProperyNameToNewPropertyName);

        return $node;
    }

    /**
     * @param string[] $componentNames
     * @return string[]
     */
    private function matchComponentClass(array $componentNames): array
    {
        $componentsClasses = $this->getComponentClasses();

        $matchedComponentClasses = [];

        foreach ($componentNames as $componentName) {
            foreach ($componentsClasses as $componentClass) {
                $shortComponentClass = $this->getShortName($componentClass);
                if (! Strings::startsWith($shortComponentClass, $componentName)) {
                    continue;
                }

                $matchedComponentClasses[$componentName] = $componentClass;
            }
        }

        return $matchedComponentClasses;
    }

    /**
     * @return string[]
     */
    private function getComponentClasses(): array
    {
        if ($this->componentsClasses !== []) {
            return $this->componentsClasses;
        }

        foreach (get_declared_classes() as $declaredClass) {
            if ($declaredClass === 'Component') {
                continue;
            }

            if (! is_a($declaredClass, 'Component', true)) {
                continue;
            }

            $this->componentsClasses[] = $declaredClass;
        }

        return $this->componentsClasses;
    }
}
