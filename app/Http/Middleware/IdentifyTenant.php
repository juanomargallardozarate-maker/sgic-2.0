<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // ✅ IGNORAR en desarrollo local (localhost, 127.0.0.1, *.test)
        if (app()->environment('local')) {
            // En local, usar el primer tenant activo como default
            $tenant = Tenant::where('is_active', true)->first();
            if ($tenant) {
                $request->attributes->set('tenant', $tenant);
                config(['app.tenant_id' => $tenant->id]);
                view()->share('currentTenant', $tenant);
            }
            return $next($request);
        }

        // ✅ IGNORAR dominios reservados
        if (in_array($host, ['localhost', '127.0.0.1', '0.0.0.0'])) {
            return $next($request);
        }

        // ✅ IGNORAR TLDs de desarrollo (.test, .local, .localhost)
        if (str_ends_with($host, '.test') || str_ends_with($host, '.local') || str_ends_with($host, '.localhost')) {
            // Extraer subdominio de *.sgic.test
            $parts = explode('.', $host);
            if (count($parts) >= 3) {
                $subdomain = $parts[0];
                
                if (!in_array($subdomain, ['www', 'admin', 'api'])) {
                    $tenant = Tenant::where('subdomain', $subdomain)
                        ->where('is_active', true)
                        ->first();
                    
                    if (!$tenant) {
                        abort(404, 'Cementerio no encontrado');
                    }
                    
                    $request->attributes->set('tenant', $tenant);
                    config(['app.tenant_id' => $tenant->id]);
                    view()->share('currentTenant', $tenant);
                }
            }
            return $next($request);
        }

        // ✅ PRODUCCIÓN: Identificar por subdominio
        $parts = explode('.', $host);
        
        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            
            // Ignorar subdominios reservados
            if (in_array($subdomain, ['www', 'admin', 'api'])) {
                return $next($request);
            }
            
            $tenant = Tenant::where('subdomain', $subdomain)
                ->where('is_active', true)
                ->first();
            
            if (!$tenant) {
                abort(404, 'Cementerio no encontrado');
            }
            
            if (!$tenant->is_active) {
                abort(402, 'Suscripción vencida. Contacte al administrador.');
            }
            
            $request->attributes->set('tenant', $tenant);
            config(['app.tenant_id' => $tenant->id]);
            view()->share('currentTenant', $tenant);
        }
        
        return $next($request);
    }
}