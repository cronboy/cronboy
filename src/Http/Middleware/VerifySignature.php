<?php

namespace Cronboy\Cronboy\Http\Middleware;

use Closure;
use Cronboy\Cronboy\Exceptions\MismatchingSignatureException;
use Cronboy\Cronboy\Exceptions\MissingSignatureException;
use Cronboy\Cronboy\Services\ParamsSign;
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

        $builtSign = $this->signParams(
            $this->configuration->get('cronboy.signature_key'), array_except($params, ['signature', 'key'])
        );

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
        return ParamsSign::make($params, $sign);
    }
}
