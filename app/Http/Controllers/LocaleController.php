<?php

namespace App\Http\Controllers;

use App\Support\ArticleLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch(Request $request, $locale)
    {
        // Region-qualified codes such as en-GB are accepted but folded down to
        // the four keys articles are actually stored under, so the locale the
        // app runs on always matches a real key in the translatable JSON.
        $normalised = ArticleLocale::normalize($locale);

        if (ArticleLocale::isSupported($normalised)) {
            Session::put('locale', $normalised);
            App::setLocale($normalised);
        }
        return redirect()->back();
    }
}
