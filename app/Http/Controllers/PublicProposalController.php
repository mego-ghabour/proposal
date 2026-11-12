<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicProposalController extends Controller
{
    /**
     * Display proposal by uuid or slug.
     */
    public function show(string $identifier): View
    {
        $proposal = Proposal::where('uuid', $identifier)
            ->orWhere('slug', $identifier)
            ->firstOrFail();

        // Increment view count and record timestamp
        $proposal->increment('views_count');
        $proposal->update(['last_viewed_at' => now()]);

        $settings = CompanySetting::current();

        return view('proposals.show', compact('proposal', 'settings'));
    }

    /**
     * Handle client acceptance.
     */
    public function accept(Request $request, string $identifier): JsonResponse
    {
        $proposal = Proposal::where('uuid', $identifier)
            ->orWhere('slug', $identifier)
            ->firstOrFail();

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'package_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $proposal->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'accepted_package_name' => $validated['package_name'],
            'client_notes' => $validated['notes'] ?? null,
            'client_phone' => $validated['client_phone'] ?? $proposal->client_phone,
        ]);

        $settings = CompanySetting::current();
        $salesPhone = $proposal->sales_phone ?: ($settings->whatsapp ?: $settings->phone);

        // Clean whatsapp number
        $waNumber = preg_replace('/[^0-9]/', '', $salesPhone);
        $waMessage = urlencode("مرحباً وكالة فكرتك الرقمية،\nأنا {$validated['client_name']}، أؤكد رغبتي في اعتماد عرض ({$proposal->title}) - باقة ({$validated['package_name']}).\nيرجى التواصل للبدء في توقيع العقد.");

        $whatsappRedirect = "https://wa.me/{$waNumber}?text={$waMessage}";

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل رغبتكم في اعتماد العرض بنجاح! يسعدنا بدء العمل معكم وسيتواصل معكم فريقنا فوراً.',
            'whatsapp_url' => $whatsappRedirect,
        ]);
    }
}
