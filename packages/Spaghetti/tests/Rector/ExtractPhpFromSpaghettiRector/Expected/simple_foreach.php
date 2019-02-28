<?php
class SimpleForeachController
{
    public function render()
    {
        $items = [1, 2, 3];
        foreach ($items as $key => $item) {
            $item *= 2;
            $items[$key] = $item;
        }
        return ['items' => $items];
    }
}

$variables = (new SimpleForeachController())->render();
extract($variables);

foreach ($items as $item) {
    echo '<strong>' . $item . '</strong>';
}