<?php

namespace App\Http\Controllers;

use Themes\DefaultTheme\src\Controllers\PageController as ThemePageController;

class StaticPageController extends Controller
{
    public function aboutUs(ThemePageController $pageController)
    {
        return $pageController->show('درباره-ما');
    }
}