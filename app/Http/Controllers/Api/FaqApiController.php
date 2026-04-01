<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;

class FaqApiController extends Controller
{
    /**
     * Get all active FAQs
     */
    public function activeFaqs(): JsonResponse
    {
        $faqs = Faq::where('status', 1)
                    ->select('id', 'question', 'answer')
                    ->latest()
                    ->get();

        return response()->json([
            'success' => true,
            'data'    => $faqs,
        ]);
    }
}
