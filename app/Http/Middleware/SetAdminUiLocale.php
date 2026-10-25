<?php

namespace App\Http\Middleware;

use App\Support\AdminUi;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** 后台界面语言，不影响前台内容语言 */
class SetAdminUiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin') || $request->is('admin/*')) {
            $ui = $request->query('ui');
            if (is_string($ui) && AdminUi::isValid($ui)) {
                $remembered = AdminUi::remember($ui, saveUser: (bool) auth('admin')->check());
                AdminUi::apply();
                $response = $this->respond($request, $next);

                $response->headers->setCookie($remembered['cookie']);

                return $response;
            }
            AdminUi::apply();
        }

        return $this->respond($request, $next);
    }

    private function respond(Request $request, Closure $next): Response
    {
        $translator = app('translator');
        $fallback = $translator->getFallback();
        if (($request->is('admin') || $request->is('admin/*')) && ! AdminUi::isChinese()) {
            $translator->setFallback('en');
        }
        try {
            return $next($request);
        } finally {
            $translator->setFallback($fallback);
        }
    }
}
