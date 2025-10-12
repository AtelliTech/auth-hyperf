<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth\Jwt;

use AtelliTech\Hyperf\Auth\Contract\JwtVerifierInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;

final class AutoJwksJwtVerifier implements JwtVerifierInterface
{
    public function __construct(
        private readonly string $jwksEndpoint,
        private readonly CachedJwksFetcher $fetcher
    ) {
    }

    /**
     * Verify the given JWT token and return its claims.
     *
     * @return array<string, mixed>
     */
    public function verify(string $token): array
    {
        // resolve the JWT header without verifying the signature
        $segments = explode('.', $token);
        if (count($segments) !== 3) {
            throw new InvalidArgumentException('Invalid JWT format.');
        }

        $header = json_decode(base64_decode($segments[0]), true);
        $kid = $header['kid'] ?? null;
        $alg = $header['alg'] ?? 'RS256';

        if (! $kid) {
            throw new InvalidArgumentException('JWT header missing "kid".');
        }

        // fetch JWKS
        $jwks = $this->fetcher->get($this->jwksEndpoint);

        // figure out the public key from JWKS
        $keyData = null;
        foreach ($jwks['keys'] ?? [] as $key) {
            if (($key['kid'] ?? '') === $kid) {
                $keyData = $key;
                break;
            }
        }

        if (! $keyData) {
            throw new InvalidArgumentException("Public key not found for kid: {$kid}");
        }

        $pem = $this->convertJwkToPem($keyData);

        // verify signature
        $decoded = JWT::decode($token, new Key($pem, $alg));
        return (array) $decoded;
    }

    /**
     * Convert a JWK to PEM format.
     *
     * @param array<string, mixed> $jwk
     */
    private function convertJwkToPem(array $jwk): string
    {
        $modulus = $this->urlsafeB64Decode($jwk['n']);
        $exponent = $this->urlsafeB64Decode($jwk['e']);

        // RSAPublicKey ::= SEQUENCE { n INTEGER, e INTEGER }
        $rsaPublicKey = "\x30" . $this->asn1Length(
            strlen("\x02" . $this->asn1Length(strlen($modulus)) . $modulus)
            + strlen("\x02" . $this->asn1Length(strlen($exponent)) . $exponent)
        )
            . "\x02" . $this->asn1Length(strlen($modulus)) . $modulus
            . "\x02" . $this->asn1Length(strlen($exponent)) . $exponent;

        // BIT STRING wrapper
        $bitString = "\x03" . $this->asn1Length(strlen($rsaPublicKey) + 1) . "\x00" . $rsaPublicKey;

        // AlgorithmIdentifier for rsaEncryption (OID 1.2.840.113549.1.1.1)
        $algorithmIdentifier = "\x30\x0d"
            . "\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01" // OID
            . "\x05\x00"; // NULL parameter

        // Final SubjectPublicKeyInfo ::= SEQUENCE
        $sequence = "\x30" . $this->asn1Length(strlen($algorithmIdentifier . $bitString))
            . $algorithmIdentifier . $bitString;

        // Encode to PEM
        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($sequence), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    /**
     * Base64 URL decode helper.
     */
    private function urlsafeB64Decode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * ASN.1 length encoding (支持 >127 bytes).
     */
    private function asn1Length(int $length): string
    {
        if ($length <= 0x7F) {
            return chr($length);
        }

        $len = ltrim(pack('N', $length), "\x00");
        return chr(0x80 | strlen($len)) . $len;
    }
}
