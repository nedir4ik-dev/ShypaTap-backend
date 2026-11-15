<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('admin.profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($data);

        return back()->with('success', 'Profil maglumatlary täzelendi.');
    }

    public function uploadAvatar(Request $request, AvatarService $avatars)
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:3072'],
        ]);

        $avatars->replace($request->user(), $request->file('avatar'));

        return back()->with('success', 'Profil suraty täzelendi.');
    }

    public function deleteAvatar(Request $request, AvatarService $avatars)
    {
        $avatars->remove($request->user());

        return back()->with('success', 'Profil suraty aýryldy.');
    }
}
