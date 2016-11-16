<?php

if (!function_exists('cronboy')) {
    /**
     * Helper function for create schedule webhooks with Cronboy SaaS Service.
     *
     * @param string|\Carbon\Carbon|null $time_to_execute
     *
     * @return \Cronboy\Cronboy\Cronboy
     */
    function cronboy($time_to_execute = null)
    {
        $cronboy = app(\Cronboy\Cronboy\Cronboy::class);

        if (is_null($time_to_execute)) {
            return $cronboy;
        }

        return $cronboy->at($time_to_execute);
    }
}
