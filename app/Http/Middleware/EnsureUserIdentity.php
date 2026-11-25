<?php

namespace App\Http\Middleware;

use App\Services\UserIdentityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIdentity
{
    public function __construct(
        protected UserIdentityService $identityService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->identityService->getCurrentUser($request);

        if (!$user) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'authenticated' => false,
                    'message' => 'Identitas sesi diperlukan. Silakan setup nama tampilan terlebih dahulu.',
                ], 401);
            }

            return redirect()->route('identity.setup')
                ->with('info', 'Silakan tentukan nama Anda sebelum melanjutkan.');
        }

        // Attach user session object to request attributes
        $request->attributes->set('currentUser', $user);

        return $next($request);
    }
}
