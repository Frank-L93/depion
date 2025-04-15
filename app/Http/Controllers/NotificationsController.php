<?php

namespace App\Http\Controllers;

class NotificationsController extends Controller
{
    public function read()
    {
        auth()->user()->unReadNotifications->markAsRead();

        return redirect()->back();
    }
}
