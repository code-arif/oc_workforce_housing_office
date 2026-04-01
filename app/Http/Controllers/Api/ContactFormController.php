<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Contact;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactForm\ContactSubmittedMail;

class ContactFormController extends Controller
{
    use ApiResponse;

    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'first_name'  => 'nullable|string|max:50',
            'last_name'   => 'nullable|string|max:50',
            'email'       => 'required|email|max:100',
            'phone'       => 'nullable|string|max:20',
            'subject'     => 'nullable|string|max:100',
            'message'     => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();

        try {
            $contact = Contact::create($data);

            // Optional: send to multiple recipients
            // Mail::to([config('mail.from.address'), 'support@example.com'])
            Mail::to(config('mail.from.address'))->send(new ContactSubmittedMail($contact));

            DB::commit();

            return $this->success(
                $contact,
                'Thank you! Your message has been sent successfully.',
                201
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'data'  => $data,
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error(
                [],
                'Something went wrong. Please try again later.',
                500
            );
        }
    }
}
