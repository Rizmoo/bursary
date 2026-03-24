<?php

namespace App\Http\Controllers;

use App\Models\County;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CountyRegistrationController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->to('/app');
        }

        return view('county-register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'county_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $countyName = trim($validated['county_name']);
        $existingCounty = County::query()
            ->whereRaw('lower(name) = ?', [mb_strtolower($countyName)])
            ->first();

        if ($existingCounty) {
            throw ValidationException::withMessages([
                'county_name' => 'This county is already registered. Please contact system admin for support.',
            ]);
        }

        [$county, $user] = DB::transaction(function () use ($validated): array {
            $county = County::query()->create([
                'name' => trim($validated['county_name']),
            ]);

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'ward_id' => null,
                'county_id' => $county->getKey(),
                'is_admin' => false,
                'is_county_admin' => true,
            ]);

            return [$county, $user];
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to('/app');
    }
}
