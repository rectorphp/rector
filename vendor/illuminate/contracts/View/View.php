<?php

namespace RectorPrefix202308\Illuminate\Contracts\View;

use RectorPrefix202308\Illuminate\Contracts\Support\Renderable;
interface View extends Renderable
{
    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function name();
    /**
     * Add a piece of data to the view.
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function with($key, $value = null);
    /**
     * Get the array of view data.
     *
     * @return array
     */
    public function getData();
}
