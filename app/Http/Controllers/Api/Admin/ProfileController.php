<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Http\Resources\UserResource;

class ProfileController extends Controller
{
    /**
     * Updates the signed-in admin's name, email, and optionally password.
     * A new password signs out every other device by revoking their tokens.
     */
    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();

        $user->fill($request->safe()->only(['name', 'email']));

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
            $currentTokenId = $user->currentAccessToken()?->getKey();
            $user->tokens()->when($currentTokenId, fn ($tokens) => $tokens->whereKeyNot($currentTokenId))->delete();
        }

        $user->save();

        return new UserResource($user);
    }
}
