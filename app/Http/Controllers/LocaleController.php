<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch(Request $request, $locale)
    {
        $supportedLocales = ['en', 'id', 'ja', 'ms', 'en-GB'];
        if (in_array($locale, $supportedLocales)) {
            Session::put('locale', $locale);
            App::setLocale($locale);
        }
        return redirect()->back();
    }
}
