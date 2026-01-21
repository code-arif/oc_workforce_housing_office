<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Show FAQ list (Admin Panel)
     */
    public function index()
    {
        $faqs = Faq::latest()->get();
        return view('backend.faq.index', compact('faqs'));
    }

    /**
     * Store new FAQ
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
        ]);

        Faq::create([
            'question' => $request->question,
            'answer'   => $request->answer,
            'status'   => 1, // default Active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FAQ Added Successfully',
        ]);
    }

    /**
     * Update existing FAQ
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
        ]);

        $faq = Faq::findOrFail($id);

        $faq->update([
            'question' => $request->question,
            'answer'   => $request->answer,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FAQ Updated Successfully',
        ]);
    }

    /**
     * Delete FAQ
     */
    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ Deleted Successfully',
        ]);
    }

    /**
     * Toggle Active / Inactive (Admin only)
     */
       public function status($id)
    {
        $faq = Faq::findOrFail($id);

        // ✅ TOGGLE STATUS
        $faq->status = !$faq->status;
        $faq->save();

        return response()->json([
            'success' => true,
            'status'  => $faq->status
        ]);
    
}
}
