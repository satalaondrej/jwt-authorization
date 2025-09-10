<?php

namespace Nalgoo\JwtAuthorization\Serialization;

use League\Uri\Http;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UriDenormalizer implements DenormalizerInterface
{
	public function denormalize(mixed $data, string $type, ?string $format = null, array $context = [])
	{
		return Http::new($data);
	}

	public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
	{
		return $type === UriInterface::class;
	}

	public function getSupportedTypes(?string $format): array
	{
		return [
			UriInterface::class => true,
		];
	}
}
