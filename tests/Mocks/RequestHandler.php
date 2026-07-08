<?php

namespace Nalgoo\JwtAuthorization\Tests\Mocks;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Will handle Request by doing nothing (and creating empty Response).
 * Last passed Request will be available with getRequest().
 */
class RequestHandler implements RequestHandlerInterface
{
	private ServerRequestInterface $request;

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$this->request = $request;

		$factory = new Psr17Factory();

		return $factory->createResponse();
	}

	public function getRequest(): ServerRequestInterface
	{
		return $this->request;
	}
}
