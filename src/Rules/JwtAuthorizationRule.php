<?php

namespace Nalgoo\JwtAuthorization\Rules;

use Psr\Http\Message\UriInterface;

readonly class JwtAuthorizationRule
{
	public function __construct(
		private string $action,
		private UriInterface $resource,
	) {
	}

	public function getAction(): string
	{
		return $this->action;
	}

	public function getResource(): UriInterface
	{
		return $this->resource;
	}
}
