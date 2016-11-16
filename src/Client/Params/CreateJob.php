<?php

namespace Cronboy\Cronboy\Client\Params;

use Carbon\Carbon;
use Cronboy\Cronboy\Client\Exceptions\InvalidArgumentsException;

/**
 * Class CreateEvent.
 */
class CreateJob
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    protected $argumentsRules = [
        'url'               => 'required|regex:/^[\w\/.-_~!$&\'()*+,;=:@]+$/',
        'verb'              => 'required|in:GET,POST',
        'time_to_execute'   => 'required|date',
        'params'            => 'array',
    ];

    /**
     * CreateEvent constructor.
     *
     * @param string $url
     * @param string $verb
     * @param Carbon $time_to_execute
     * @param array  $params
     */
    public function __construct($url, $verb, Carbon $time_to_execute, array $params)
    {
        // Format request params
        $this->data = [
            'url'             => $url,
            'verb'            => $verb,
            'params'          => $params,
            'time_to_execute' => $time_to_execute->tz('UTC'),
        ];

        // Validate params
        $this->validate();
    }

    /**
     * @throws \Exception
     */
    protected function validate()
    {
        $validator = app('validator')->make($this->data, $this->argumentsRules);

        $validator->after(function ($validator) {
            if ($this->data['time_to_execute']->lt(Carbon::now('UTC'))) {
                $validator->errors()->add('time_to_execute', 'The time to execute must be a date after now.');
            }
        });

        if ($validator->fails()) {
            throw new InvalidArgumentsException($this->data, $validator->errors(), 422);
        }
    }

    /**
     * @return string
     */
    public function toJson()
    {
        // Convert Carbon object to string for API call
        $this->data['time_to_execute'] = $this->data['time_to_execute']->toDateTimeString();

        return \GuzzleHttp\json_encode(
            $this->data
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        // Convert Carbon object to string for API call
        $this->data['time_to_execute'] = $this->data['time_to_execute']->toDateTimeString();

        return $this->data;
    }
}
