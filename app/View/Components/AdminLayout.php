<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class AdminLayout extends Component
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        // Ini akan mengarahkan Laravel untuk menggunakan file layout Anda yang sudah ada
        return view('layouts.admin');
    }
}