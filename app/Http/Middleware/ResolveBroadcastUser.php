<?php

namespace App\Http\Middleware;

use App\Services\UserIdentityService;
use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveBroadcastUser
{
    public function __construct(
        protected UserIdentityService $identityService
    ) {}

    /**
     * Bind SecureLink's session-based UserSession to the request user resolver
     * so Laravel Broadcasting's /broadcasting/auth can authorize private/presence channels.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionUser = $this->identityService->getCurrentUser($request);

        if ($sessionUser) {
            $request->setUserResolver(fn () => new GenericUser([
                'id' => $sessionUser->user_id,
                'user_id' => $sessionUser->user_id,
                'display_name' => $sessionUser->display_name,
                'avatar_color' => $sessionUser->avatar_color,
            ]));
        }

        return $next($request);
    }
}
