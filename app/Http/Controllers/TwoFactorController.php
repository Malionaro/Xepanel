<?php

namespace App\Http\Controllers;

use App\Models\FileUser;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    public function showSetup()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();

        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('users.2fa', compact('qrCodeSvg', 'user'));
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        
        $user = Auth::user();
        $google2fa = new Google2FA();
        
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            $user->two_factor_enabled = true;
            $user->save();
            ActivityLog::log("Enabled 2FA", "User: {$user->email}");
            return back()->with('status', 'Two-Factor Authentication has been enabled successfully!');
        }

        return back()->withErrors(['code' => 'Invalid authentication code.']);
    }

    public function disable(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        
        $user = Auth::user();
        $google2fa = new Google2FA();
        
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            $user->two_factor_enabled = false;
            $user->two_factor_secret = null;
            $user->save();
            ActivityLog::log("Disabled 2FA", "User: {$user->email}");
            return back()->with('status', 'Two-Factor Authentication has been disabled.');
        }

        return back()->withErrors(['code' => 'Invalid authentication code.']);
    }

    public function prompt(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = FileUser::findById($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            $request->session()->forget('2fa_user_id');
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors(['code' => 'The provided two-factor authentication code was invalid.']);
    }
}
