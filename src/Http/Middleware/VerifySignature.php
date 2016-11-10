<?php

namespace Cronboy\Cronboy\Http\Middleware;

use Closure;
use Cronboy\Cronboy\Exceptions\MismatchingSignatureException;
use Cronboy\Cronboy\Exceptions\MissingSignatureException;
use Illuminate\Contracts\Config\Repository;

/**
 * Class VerifySignature.
 */
class VerifySignature
{
    /**
     * @var Repository
     */
    protected $configuration;

    /**
     * VerifySignature constructor.
     *
     * @param Repository $configuration
     */
    public function __construct(Repository $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     * @param null                     $guard
     *
     * @throws MismatchingSignatureException
     * @throws MissingSignatureException
     *
     * @return
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $params = $request->input();

        $receivedSign = array_get($params, 'signature');

        if (empty($receivedSign)) {
            throw new MissingSignatureException();
        }

        $signatureComponents = array_except($params, ['signature', 'key']);
        $builtSign = $this->signParams($this->configuration->get('cronboy.app_secret'), $signatureComponents);

        if ($receivedSign != $builtSign) {
            throw new MismatchingSignatureException(sprintf('%s | %s', $receivedSign, $builtSign));
        }

        return $next($request);
    }

    /**
     * @param string $sign
     * @param array  $params
     *
     * @return string
     */
    protected function signParams($sign, $params)
    {
        $signatureParts = array_dot($params);
        ksort($signatureParts);
        array_push($signatureParts, $sign);

        return hash('sha256', implode($signatureParts));
    }
}
