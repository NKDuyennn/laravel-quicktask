<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
class LanguageController extends Controller
{
    //
    public function changeLanguage(Request $request, $language)
    {
        Session::put('lang', $language);

        // Trở về trang trước đó
        return redirect()->back();
    }
}

?>
