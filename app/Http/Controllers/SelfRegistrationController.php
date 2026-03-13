<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SelfRegistrationController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            $tenantId = $request->user()?->ward_id;

            return redirect()->to($tenantId ? "/admin/{$tenantId}" : '/admin');
        }

        return view('landing');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'ward_name' => ['required', 'string', 'max:255'],
        ]);

        $wardName = trim($validated['ward_name']);

        $existingWard = Ward::query()
            ->whereRaw('lower(name) = ?', [mb_strtolower($wardName)])
            ->first();

        if ($existingWard) {
            throw ValidationException::withMessages([
                'ward_name' => 'This ward is already registered. Please contact your Bursary admin to help you log in.',
            ]);
        }

        [$ward, $user] = DB::transaction(function () use ($validated): array {
            $wardName = trim($validated['ward_name']);

            $ward = Ward::create([
                'name' => $wardName,
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'ward_id' => $ward->getKey(),
                'is_admin' => false,
            ]);

            return [$ward, $user];
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to('/admin/' . $ward->getKey());
    }
}
