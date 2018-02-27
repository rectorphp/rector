<?php declare(strict_types=1);


final class LegacyController
{
    public function actionRelax()
    {
        $bartender = $this->get('bartender');
        $bartender->makeCoffee();
    }
}
