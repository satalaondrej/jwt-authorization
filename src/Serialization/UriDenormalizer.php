<?php
declare(strict_types=1);

namespace Nalgoo\JwtAuthorization\Serialization;

use League\Uri\Http;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UriDenormalizer implements DenormalizerInterface
{
	/**
	 * @param array<string, mixed> $context
	 */
	public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): UriInterface
	{
		if (!is_string($data)) {
			throw NotNormalizableValueException::createForUnexpectedDataType('The data to denormalize into a URI must be a string.', $data, ['string']);
		}

		return Http::new($data);
	}

	/**
	 * @param array<string, mixed> $context
	 */
	public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
	{
		return $type === UriInterface::class;
	}

	/**
	 * @return array<class-string, bool>
	 */
	public function getSupportedTypes(?string $format): array
	{
		return [
			UriInterface::class => true,
		];
	}
}
