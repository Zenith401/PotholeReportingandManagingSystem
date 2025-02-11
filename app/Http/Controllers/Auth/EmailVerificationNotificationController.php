<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        \Log::info('EmailVerificationNotificationController@store method called', [
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
        ]);
    
        if ($request->user()->hasVerifiedEmail()) {
            \Log::info('User already verified, redirecting to dashboard');
            return redirect()->intended(route('dashboard'));
        }
    
        try {
            $request->user()->sendEmailVerificationNotification();
            $status = session('email_update') ? 'email-update-verification-sent' : 'verification-link-sent';
    
            session()->flash('status', $status);
            \Log::info('Verification notification sent', [
                'status' => $status,
                'session' => session()->all(),
            ]);
    
            if ($status === 'email-update-verification-sent') {
                session()->forget('email_update');
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send verification notification', [
                'error' => $e->getMessage(),
            ]);
        }
    
        return back();
    }
    
}
