<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdminOrAccountant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !in_array($user->type, ['admin', 'employee'])) {
            abort(403, 'Unauthorized');
        }

        // تحقق إذا كان المستخدم أدمن أو موظف ومسوق
        if ($user && ( $user->type === 'admin' || ($user->type === 'employee' && (bool) $user->is_accountant))) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
