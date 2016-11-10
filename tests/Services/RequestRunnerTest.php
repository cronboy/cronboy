<?php

/**
 * Created by PhpStorm.
 * User: stas
 * Date: 13.10.16
 * Time: 2:23.
 */
class RequestRunnerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage handled
     */
    public function it_unserializes_and_runs_closure()
    {
        $serializer = new \Cronboy\Cronboy\Services\SerializerService();
        $serializedClosure = $serializer->serializeClosure(function () {
            throw new Exception('handled');
        });

        $request = new \Illuminate\Http\Request([], [
            'closure' => $serializedClosure,
        ]);

        $rr = new \Cronboy\Cronboy\Services\RequestRunner();
        $rr->run($request);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage handled
     */
    public function it_unserializes_and_runs_job()
    {
        $serializer = new \Cronboy\Cronboy\Services\SerializerService();
        $serializedJob = $serializer->serializeJob(new RequestRunnerDummyJob());

        $request = new \Illuminate\Http\Request([], [
            'job' => $serializedJob,
        ]);

        $rr = new \Cronboy\Cronboy\Services\RequestRunner();
        $rr->run($request);
    }
}

class RequestRunnerDummyJob
{
    /**
     * @throws Exception
     */
    public function handle()
    {
        throw new Exception('handled');
    }
}
