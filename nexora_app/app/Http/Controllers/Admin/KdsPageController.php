<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;

class KdsPageController extends Controller
{
    public function index()
    {
        return view('kds.index', ['type' => 'kitchen']);
    }
}

