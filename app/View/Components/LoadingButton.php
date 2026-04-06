<?php

namespace App\View\Components;

use Illuminate\View\Component;

class LoadingButton extends Component
{
    public $text;
    public $class;

    public function __construct($text = 'Submit', $class = '')
    {
        $this->text = $text;
        $this->class = $class;
    }

    public function render()
    {
        return view('components.loading-button');
    }
}
