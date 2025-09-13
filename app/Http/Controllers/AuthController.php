<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'usertype' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
            'recaptchaValue' => 'required|string',
        ]);


        // Verify reCAPTCHA
        $recaptchaSecret = env('RECAPTCHA_SECRET_KEY');
        $recaptchaValue = $request->recaptchaValue;
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $recaptchaSecret,
            'response' => $recaptchaValue,
        ]);

        if (!$recaptchaResponse->json('success')) {
            return response()->json(['message' => 'reCAPTCHA verification failed'], 422);
        }

        $activationCode = bin2hex(random_bytes(32));
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'usertype' => $request->usertype,
            'password' => Hash::make($request->password),
            'activationCode' => $activationCode,
            'isActive' => false,
        ]);

        // Generate verification link with random token
        $verificationLink = url('/api/verify-email/' . $activationCode);
        Mail::to($user->email)->send(new EmailVerificationMail($verificationLink));

        return response()->json(['message' => 'User registered successfully. Please check your email to verify your account.'], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // For demonstration, just return success (token logic can be added)
        return response()->json(['message' => 'Login successful']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent']);
        }

        return response()->json(['message' => 'Unable to send reset link'], 400);
    }

    public function verifyEmail($token)
    {
        if (!$token) {
            return response()->json(['message' => 'Activation token is required'], 400);
        }

        $user = User::where('activationCode', $token)->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired activation token'], 400);
        }

        $user->isActive = true;
        $user->activationCode = null;
        $user->save();

        return response()->json(['message' => 'Email verified successfully']);
    }
}
