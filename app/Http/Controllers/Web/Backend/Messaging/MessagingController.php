<?php

namespace App\Http\Controllers\Web\Backend\Messaging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessagingController extends Controller
{
    /**
     * Show messaging inbox page
     */
    public function index()
    {
        return view('backend.layouts.messaging.index');
    }

    /**
     * Show messaging compose page
     */
    public function compose()
    {
        return view('backend.layouts.messaging.compose');
    }


    /**
     * Show messaging compose page
     */
    public function read()
    {
        return view('backend.layouts.messaging.read');
    }
}
