<?php

namespace Cronboy\Cronboy\Services;

/**
 * Class ParamsSignature.
 */
class ParamsSign
{
    /**
     * @param $params
     * @param $secret
     *
     * @return mixed
     */
    public static function make($params, $secret)
    {
        $signatureParts = array_dot($params);
        ksort($signatureParts);
        array_push($signatureParts, $secret);

        return hash('sha256', implode($signatureParts));
    }
}
