<?php declare (strict_types=1);

namespace App\Controller;

class AppController extends AbstractController
{
    /**
     * @Template()
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return [];
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}