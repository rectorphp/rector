<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteControlToSymfonyControllerRector\Fixture;

use App\Service\BasketManager;
use Nette\Application\UI\Control;

class TemplateWithAssign extends Control
{
    /**
     * @var \App\Service\BasketManager
     */
    private $basketManager;

    public function __construct(BasketManager $basketManager)
    {
        $this->basketManager = $basketManager;
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/templates/default.latte');

        $this->template->items = $this->basketManager->getItems();
        $this->template->discountCode = $this->basketManager->getDiscountCode();

        $this->template->render();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteControlToSymfonyControllerRector\Fixture;

use App\Service\BasketManager;
use Nette\Application\UI\Control;

class TemplateWithAssignController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /**
     * @var \App\Service\BasketManager
     */
    private $basketManager;

    public function __construct(BasketManager $basketManager)
    {
        $this->basketManager = $basketManager;
    }

    public function action(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render(__DIR__ . '/templates/default.latte', ['items' => $this->basketManager->getItems(), 'discountCode' => $this->basketManager->getDiscountCode()]);
    }
}

?>
