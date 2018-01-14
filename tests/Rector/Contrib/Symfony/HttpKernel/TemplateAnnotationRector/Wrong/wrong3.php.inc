<?php declare (strict_types=1);

class ClassWithNamedService1 extends Controller
{
    /**
     * @template("someFile.toBe.used")
     */
    public function indexAction()
    {
        return [];
    }
}
