<?php

use App\Models\Settings;
use Illuminate\Support\Facades\Auth;


function settings($key = null)
{
    if(Auth::guest()) {
        return null;
    }
    $currentSettings = Auth::user()->settings;
    $settings = new Settings($currentSettings, Auth::user());

    return $key ? $settings->get($key) : $settings;
}
