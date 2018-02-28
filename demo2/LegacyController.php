<?php declare(strict_types=1);

namespace Demo2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class LegacyController extends Controller
{
    public function actionRelax(): void
    {
        $bartender = $this->get('bartender');
        $bartender->makeCoffee();
    }
}
