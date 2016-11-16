<?php

use Cronboy\Cronboy\Services\SerializerService;

class SerializerServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider closuresForSerialization
     */
    public function it_serializes_closure($originalClosure, $signature, $serializedClosure)
    {
        $service = new SerializerService($signature);
        $actualString = $service->serializeClosure($originalClosure);
        $this->assertEquals($serializedClosure, $actualString);
    }

    /**
     * @test
     * @dataProvider closuresForSerialization
     */
    public function it_unserializes_closure($originalClosure, $signature, $serializedClosure)
    {
        $service = new SerializerService($signature);
        $unserializedClosure = $service->unserializeClosure($serializedClosure);
        $this->assertEquals($originalClosure, $unserializedClosure);
    }

    /**
     * @test
     * @dataProvider notValidClosures
     * @expectedException \Cronboy\Cronboy\Exceptions\ClosureUnserializationException
     */
    public function it_throws_exception_when_not_valid_string_closure_passed($signature, $notValidClosure)
    {
        $service = new SerializerService($signature);
        $service->unserializeClosure($notValidClosure);
    }

    /**
     * @test
     * @dataProvider jobsForSerialization
     */
    public function it_serializes_job($originalJob, $signature, $serializedJob)
    {
        $service = new SerializerService($signature);
        $actualString = $service->serializeJob($originalJob);
        $this->assertEquals($serializedJob, $actualString);
    }

    /**
     * @test
     * @dataProvider jobsForSerialization
     */
    public function it_unserializes_job($originalJob, $signature, $serializedJob)
    {
        $service = new SerializerService($signature);
        $unserializedClosure = $service->unserializeJob($serializedJob);
        $this->assertEquals($originalJob, $unserializedClosure);
    }

    /**
     * @test
     * @dataProvider notValidJobs
     * @expectedException \Cronboy\Cronboy\Exceptions\ClosureUnserializationException
     */
    public function it_throws_exception_when_not_valid_string_job_passed($signature, $notValidJob)
    {
        $service = new SerializerService($signature);
        $service->unserializeJob($notValidJob);
    }

    /**
     * @return array
     */
    public function closuresForSerialization()
    {
        return [
            'null signature' => [
                function () {
                    return 'hello world';
                },
                null,
                <<<HEREDOC
C:32:"SuperClosure\SerializableClosure":163:{a:5:{s:4:"code";s:42:"function () {
    return 'hello world';
};";s:7:"context";a:0:{}s:7:"binding";N;s:5:"scope";s:21:"SerializerServiceTest";s:8:"isStatic";b:0;}}
HEREDOC
            ],
            'string signature' => [
                function () {
                    return 'hello world';
                },
                'closure signature',
                <<<HEREDOC
%q1mnk34pqU1mbCJ359dCbT72whR0seSH44KtxyB8ilA=C:32:"SuperClosure\SerializableClosure":163:{a:5:{s:4:"code";s:42:"function () {
    return 'hello world';
};";s:7:"context";a:0:{}s:7:"binding";N;s:5:"scope";s:21:"SerializerServiceTest";s:8:"isStatic";b:0;}}
HEREDOC
            ],
        ];
    }

    /**
     * @return array
     */
    public function notValidClosures()
    {
        return [
            'null' => [
                null,
                null,
            ],
            'text' => [
                null,
                'foo bar baz',
            ],
            'mismatching signature' => [
                'fake signature',
                <<<HEREDOC
%q1mnk34pqU1mbCJ359dCbT72whR0seSH44KtxyB8ilA=C:32:"SuperClosure\SerializableClosure":163:{a:5:{s:4:"code";s:42:"function () {
    return 'hello world';
};";s:7:"context";a:0:{}s:7:"binding";N;s:5:"scope";s:21:"SerializerServiceTest";s:8:"isStatic";b:0;}}
HEREDOC
            ],
        ];
    }

    /**
     * @return array
     */
    public function jobsForSerialization()
    {
        return [
            'null signature' => [
                new DummyJob(),
                null,
                'O:8:"DummyJob":1:{s:8:"property";N;}',
            ],
            'string signature' => [
                new DummyJob('foo'),
                'closure signature',
                'O:8:"DummyJob":1:{s:8:"property";s:3:"foo";}',
            ],
        ];
    }

    /**
     * @return array
     */
    public function notValidJobs()
    {
        return [
            'text' => [
                'foo bar',
                'foo bar baz',
            ],
        ];
    }
}

class DummyJob implements \Illuminate\Contracts\Queue\Job
{
    /**
     * @var string
     */
    public $property;

    /**
     * DummyJob constructor.
     *
     * @param $property
     */
    public function __construct($property = null)
    {
        $this->property = $property;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        // TODO: Implement fire() method.
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * Determine if the job has been deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        // TODO: Implement isDeleted() method.
    }

    /**
     * Release the job back into the queue.
     *
     * @param int $delay
     *
     * @return void
     */
    public function release($delay = 0)
    {
        // TODO: Implement release() method.
    }

    /**
     * Determine if the job has been deleted or released.
     *
     * @return bool
     */
    public function isDeletedOrReleased()
    {
        // TODO: Implement isDeletedOrReleased() method.
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        // TODO: Implement attempts() method.
    }

    /**
     * Get the name of the queued job class.
     *
     * @return string
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }

    /**
     * Get the resolved name of the queued job class.
     *
     * @return string
     */
    public function resolveName()
    {
        // TODO: Implement resolveName() method.
    }

    /**
     * Call the failed method on the job instance.
     *
     * @param \Throwable $e
     *
     * @return void
     */
    public function failed($e)
    {
        // TODO: Implement failed() method.
    }

    /**
     * Get the name of the queue the job belongs to.
     *
     * @return string
     */
    public function getQueue()
    {
        // TODO: Implement getQueue() method.
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        // TODO: Implement getRawBody() method.
    }
}
