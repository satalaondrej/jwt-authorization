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
	const string PRIVATE_KEY = 'ougbDwhdV3dFaryfuIfZPznuMu77nBx/AidxkZY9iZuIgvBnejBEvhh9MRjBpK8tEPRSYBHDPjdEd5HLHZHR3w==';
	const string PUBLIC_KEY  = 'iILwZ3owRL4YfTEYwaSvLRD0UmARwz43RHeRyx2R0d8=';

	/**
	 * @var array<int, array{action: string, resource: string, jwt: string}>
	 */
	private array $tokens = [
		['action' => 'test', 'resource' => 'urn:test', 'jwt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJFZERTQSJ9.eyJpYXQiOjE3NTc1MDc0MTksIm5iZiI6MTc1NzUwNzQxOSwiZXhwIjoyMDczMDQwMjE5LCJhY2Nlc3MiOlt7ImFjdGlvbiI6InRlc3QiLCJyZXNvdXJjZSI6InVybjp0ZXN0In1dfQ.akAjGV9pXdOy2020WQJng7E4gQRZe0NJfkSKfj2fzKILLUMRBuXNqPlZq-f9bighuO2dwjV-57DdQ-yeA5ODDg'],
	];

	public function testProcess(): void
	{
		$handler = new RequestHandler();

		$factory = new Psr17Factory();
		$requestIn = $factory->createServerRequest('GET', '/')
			->withHeader('Authorization', 'Bearer ' . $this->tokens[0]['jwt']);

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
		$this->assertEquals('urn:test', (string) $rule->getResource());
	}
}
