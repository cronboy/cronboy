<?php

use Cronboy\Cronboy\Client\CronboyDevelop;

/**
 * Class CronboyDevelopTest.
 */
class CronboyDevelopTest extends Orchestra\Testbench\TestCase
{
    /** @test */
    public function it_make_a_request_for_correct_route_with_correct_params()
    {
        $cronboyDevelopClient = new CronboyDevelop();

        Route::get('route/to/execute', function () {
            $this->assertEquals(233, request()->get('id'));
            $this->assertEquals('process', request()->get('action'));
        });

        $cronboyDevelopClient->createJob('route/to/execute', 'GET', ['id' => 233, 'action' => 'process'], Carbon\Carbon::now()->addMinute());
    }
}
