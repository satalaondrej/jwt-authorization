<?php
declare(strict_types=1);

namespace Nalgoo\JwtAuthorization\Tests;

use Nalgoo\JwtAuthorization\JwtAuthorizationMiddleware;
use Nalgoo\JwtAuthorization\Rules\JwtAuthorizationRule;
use Nalgoo\JwtAuthorization\Tests\Mocks\RequestHandler;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class JwtAuthorizationMiddlewareTest extends TestCase
{
	public const PRIVATE_KEY = 'ougbDwhdV3dFaryfuIfZPznuMu77nBx/AidxkZY9iZuIgvBnejBEvhh9MRjBpK8tEPRSYBHDPjdEd5HLHZHR3w==';
	public const PUBLIC_KEY = 'iILwZ3owRL4YfTEYwaSvLRD0UmARwz43RHeRyx2R0d8=';

	/**
	 * @var array<int, array{action: string, resource: string, jwt: string}>
	 */
	private array $tokens = [
		['action' => 'test', 'resource' => 'urn:test:resource', 'jwt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJFZERTQSJ9.eyJpYXQiOjE3ODMzMzYzOTQsIm5iZiI6MTc4MzMzNjM5NCwiZXhwIjoyMDk4OTU1NTk0LCJhY2Nlc3MiOlt7ImFjdGlvbiI6InRlc3QiLCJyZXNvdXJjZSI6InVybjp0ZXN0OnJlc291cmNlIn1dfQ.b-iVWBpeONppQPGvVAd5qUqViml_tftfJtTj4syHj2QQ4qDxyAFOg3ymV9r5bg8jX2QyTutsjti5lFKd3lUGAA'],
	];

	public function testProcess(): void
	{
		$handler = new RequestHandler();

		$factory = new Psr17Factory();
		$requestIn = $factory->createServerRequest('GET', '/')
			->withHeader('Authorization', 'Bearer '.$this->tokens[0]['jwt']);

		$middleware = JwtAuthorizationMiddleware::create(self::PUBLIC_KEY);
		$middleware->process($requestIn, $handler);

		$requestOut = $handler->getRequest();
		$rules = $requestOut->getAttribute('jwt_rules');

		$this->assertIsArray($rules);
		$this->assertCount(1, $rules);
		$this->assertContainsOnlyInstancesOf(JwtAuthorizationRule::class, $rules);

		/** @var JwtAuthorizationRule $rule */
		$rule = $rules[0];
		$this->assertEquals('test', $rule->getAction());
		$this->assertEquals('urn:test:resource', (string) $rule->getResource());
	}
}
