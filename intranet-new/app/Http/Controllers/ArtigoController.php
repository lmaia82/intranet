<?php

namespace App\Http\Controllers;

class ArtigoController extends Controller
{
    public function index()
    {
        return view('artigos.index');
    }
}
