<?php

namespace Internexus\WatcherLaravel\Middlewares;

use Internexus\WatcherLaravel\Facades\Watcher;

use Symfony\Component\HttpKernel\TerminableInterface;
use Illuminate\Support\Facades\Auth;

class WebRequestMonitoring implements TerminableInterface
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, \Closure $next)
    {
        if (! Watcher::isCapturing()) {
            $this->startTransaction($request);
        }

        return $next($request);
    }

    /**
     * Start a transaction for the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     */
    protected function startTransaction($request)
    {
        if (! $this->shouldMonitor($request)) {
            return;
        }

        Watcher::transaction(
            $this->buildTransactionName($request)
        );

        if (Auth::check()) {
            Watcher::current()->withUser(Auth::user()->getAuthIdentifier());
        }
    }

    /**
     * Determine if the given request should be monitored.
     *
     * @param  JobProcessing  $event
     * @return boolean
     */
    protected function shouldMonitor($request)
    {
        $notAllowed = config('watcher.ignore_url');

        foreach ($notAllowed as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Terminates a request/response cycle.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     */
    public function terminate($request, $response)
    {
        if (Watcher::isCapturing()) {
            Watcher::current()->setResult($response->getStatusCode());

            Watcher::current()
                ->addContext('request', $request->request->all())
                ->addContext('response', [
                    'status_code' => $response->getStatusCode(),
                    'version' => $response->getProtocolVersion(),
                    'charset' => $response->getCharset(),
                    'headers' => $response->headers->all(),
                ]);
        }
    }

    /**
     * Generate readable name.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function buildTransactionName($request)
    {
        return $request->method() . ' /' . ltrim($request->path(), '/');
    }
}
