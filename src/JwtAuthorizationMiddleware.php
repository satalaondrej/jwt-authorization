<?php
declare(strict_types=1);

namespace Nalgoo\JwtAuthorization;

use DateInterval;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Eddsa;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Nalgoo\JwtAuthorization\Exceptions\JwtAuthorizationException;
use Nalgoo\JwtAuthorization\Rules\JwtAuthorizationRule;
use Nalgoo\JwtAuthorization\Serialization\UriDenormalizer;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

readonly class JwtAuthorizationMiddleware implements MiddlewareInterface
{
	/**
	 * @param non-empty-string $publicKey
	 */
	public function __construct(
		private JwtFacade             $jwtFacade,
		private ClockInterface        $clock,
		private DenormalizerInterface $denormalizer,
		private string                $publicKey,
	) {
	}

	/**
	 * @param non-empty-string $publicKey
	 */
	public static function create(string $publicKey): JwtAuthorizationMiddleware
	{
		$serializer = new Serializer([
			new UriDenormalizer(),
			new ArrayDenormalizer(),
			new ObjectNormalizer(),
		]);

		return new self(
			new JwtFacade(),
			self::getClockImplementation(),
			$serializer,
			$publicKey,
		);
	}

	public static function getClockImplementation(): ClockInterface
	{
		if (class_exists('Lcobucci\Clock\SystemClock')) {
			return \Lcobucci\Clock\SystemClock::fromUTC();
		}

		if (class_exists('Symfony\Component\Clock\Clock;')) {
			return new \Symfony\Component\Clock\NativeClock();
		}

		throw new RuntimeException('Cannot find Clock implementation');
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$authorizedRules = $this->parseAuthorizationHeader($request);

		$request = $request->withAttribute('jwt_rules', $authorizedRules);

		return $handler->handle($request);
	}

	/**
	 * @return JwtAuthorizationRule[]
	 */
	private function parseAuthorizationHeader(ServerRequestInterface $request): array
	{
		if ($request->hasHeader('Authorization')) {
			$authorizationHeader = $request->getHeaderLine('Authorization');

			if (preg_match('/^Bearer\s+(.+)$/', $authorizationHeader, $matches)) {
				$token = $matches[1];

				try {
					$parsedToken = $this->jwtFacade->parse(
						$token,
						new Constraint\SignedWith(new Eddsa(), Key\InMemory::base64Encoded($this->publicKey)),
						// jwt v4 wants a Lcobucci\Clock\Clock, v5 a PSR clock; caller must supply a compatible one.
						new Constraint\LooseValidAt($this->clock, new DateInterval('PT5S')),
					);
				} catch (InvalidTokenStructure|RequiredConstraintsViolated $e) {
					throw new JwtAuthorizationException('Invalid token' , 0, $e);
				}

				if ($parsedToken->claims()->has('access')) {
					try {
						/** @var JwtAuthorizationRule[] $rules */
						$rules = $this->denormalizer->denormalize(
							$parsedToken->claims()->get('access'),
							JwtAuthorizationRule::class . '[]',
						);

						return $rules;
					} catch (\Symfony\Component\Serializer\Exception\ExceptionInterface $e) {
						throw new JwtAuthorizationException('Invalid token', 0, $e);
					}
				}
			}
		}

		return [];
	}
}
