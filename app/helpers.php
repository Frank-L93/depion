<?php


function settings($key = null)
{
    $settings = app('App\Models\Settings');

    return $key ? $settings->get($key) : $settings;
}
