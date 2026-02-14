<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\SetupController;
use Symfony\Component\HttpFoundation\Response;

class EnsureCtiDatabaseReady
{
    /**
     * Redirect to /setup if any required CTI table is missing.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!SetupController::ctiTablesReady()) {
                return redirect()->route('setup');
            }
        } catch (\Throwable $e) {
            // DB connection error — also redirect to setup
            return redirect()->route('setup');
        }

        return $next($request);
    }
}
