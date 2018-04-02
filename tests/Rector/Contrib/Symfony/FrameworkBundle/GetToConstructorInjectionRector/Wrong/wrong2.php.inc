<?php declare (strict_types=1);

use App\Service\Anything;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ClassWithNamedService extends Controller
{
    public function render()
    {
        $this->renderTwig([
            'posts' => $this->get(\App\Service\Something::class)->callMe(),
            'comments' => $this->get(Anything::class)->callMe(),
        ]);
    }
}
