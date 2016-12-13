<?php

use \Mockery as m;
use Cronboy\Cronboy\Http\Middleware\VerifySignature;
use Illuminate\Contracts\Config\Repository;

class VerifySignatureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Cronboy\Cronboy\Exceptions\MismatchingSignatureException
     */
    public function it_throws_exception_when_signature_is_not_presented()
    {
        $configAccessor = m::mock(Repository::class);
        $configAccessor
            ->shouldReceive('get')
            ->with('cronboy.signature_key')
            ->andReturn('');

        $r = m::mock('\StdClass');
        $r
            ->shouldReceive('input')
            ->once()
            ->andReturn([]);
        $next = function () {
            throw new \Exception('$next executed');
        };

        $verifySignatureMiddleware = new VerifySignature($configAccessor);
        $verifySignatureMiddleware->handle($r, $next);
    }

    /**
     * @test
     * @expectedException \Cronboy\Cronboy\Exceptions\MismatchingSignatureException
     */
    public function it_throws_exception_when_passed_signature_is_not_expected()
    {
        $configAccessor = m::mock(Repository::class);
        $configAccessor
            ->shouldReceive('get')
            ->with('cronboy.signature_key')
            ->andReturn('');

        $r = m::mock('\StdClass');
        $r
            ->shouldReceive('input')
            ->once()
            ->andReturn([
                'signature' => 'foo',
                'param1'    => 'val1',
                'param2'    => 'val2',
            ]);
        $next = function () {
            throw new \Exception('$next executed');
        };

        $verifySignatureMiddleware = new VerifySignature($configAccessor);
        $verifySignatureMiddleware->handle($r, $next);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage $next executed
     */
    public function it_executes_next_closure_when_signature_validated()
    {
        $configAccessor = m::mock(Repository::class);
        $configAccessor
            ->shouldReceive('get')
            ->with('cronboy.signature_key')
            ->andReturn('sercret');

        $r = m::mock('\StdClass');
        $r
            ->shouldReceive('input')
            ->once()
            ->andReturn([
                'signature' => 'a1e76366f3f5145bea359c04494225e85df7e4cee1cf31e7a7a6ca724e7643aa',
                'param1'    => 'val1',
                'param2'    => 'val2',
            ]);
        $next = function () {
            throw new \Exception('$next executed');
        };

        $verifySignatureMiddleware = new VerifySignature($configAccessor);
        $verifySignatureMiddleware->handle($r, $next);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage $next executed
     */
    public function it_validates_signature_of_json_request()
    {
        $configAccessor = m::mock(Repository::class);
        $configAccessor
            ->shouldReceive('get')
            ->once()
            ->with('cronboy.signature_key')
            ->andReturn('sercret');

        $r = m::mock('\StdClass');
        $r
            ->shouldReceive('input')
            ->once()
            ->andReturn([
                'signature' => 'a1e76366f3f5145bea359c04494225e85df7e4cee1cf31e7a7a6ca724e7643aa',
                'param1'    => 'val1',
                'param2'    => 'val2',
            ]);
        $next = function () {
            throw new \Exception('$next executed');
        };

        $verifySignatureMiddleware = new VerifySignature($configAccessor);
        $verifySignatureMiddleware->handle($r, $next);
    }

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}
