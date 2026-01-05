<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\LeaseTemplate;
use Illuminate\View\View;

class LeaseTemplateController extends Controller
{
    /**
     * Display lease templates management page
     *
     * @return View
     */
    public function index(): View
    {
        return view('backend.lease.templates.index');
    }
}
