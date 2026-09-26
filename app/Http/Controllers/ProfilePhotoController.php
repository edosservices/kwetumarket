<?php

namespace App\Http\Controllers;

use App\Services\Catalog\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfilePhotoController extends Controller
{
    public function show(Request $request): StreamedResponse
    {
        $avatar = $request->user()->avatar;
        abort_unless($avatar && Storage::disk('local')->exists($avatar), 404);

        return Storage::disk('local')->response($avatar, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function update(Request $request, MediaStorage $media): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'file', 'max:'.(int) config('twende.media.max_kilobytes'), 'mimes:jpg,jpeg,png,webp,gif', 'extensions:jpg,jpeg,png,webp,gif'],
        ]);

        $user = $request->user();
        $media->delete($user->avatar, 'local');
        $user->forceFill([
            'avatar' => $media->store($request->file('avatar'), 'avatars/'.$user->id, 'local'),
        ])->save();

        return redirect()->route('profile.edit')->with('status', 'avatar-updated');
    }
}
