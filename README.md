# 🚀 atelliTech/auth-hyperf

**A flexible authentication library for Hyperf**, designed for stateless APIs with multiple authentication methods (e.g., Bearer Token and Query Parameter).
Supports JWT verification via remote JWKS (SSO integration).

---

## 🧩 Requirements

* PHP ≥ 8.3
* Hyperf 3.1 (`~3.1.0`)
* Composer ≥ 2.0

This release line supports Hyperf 3.1 only. For Hyperf 3.2, install `~3.2.0` (`v3.2.x`, branch `3.2`).

---

## ⚙️ 1. Installation

```bash
composer require atellitech/auth-hyperf:~3.1.0
```

---

## 🧰 2. Configuration

Publish the package configuration file:

```bash
php bin/hyperf.php vendor:publish atellitech/auth-hyperf
```

Then edit the file `config/autoload/auth.php`:

```php
return [
    'jwt' => [
        'jwks_url' => 'https://sso.company.com/.well-known/jwks.json',
        'ttl' => 7200, // token lifetime in seconds
        'cache' => [
            'driver' => 'default', // use your application's cache driver
        ],
    ],
];
```

> 💡 `jwks_url` should point to your SSO provider’s JWKS endpoint.

---

## 👤 3. Implement `IdentityInterface`

Create your application’s identity class implementing the interface:

```php
<?php
declare(strict_types=1);

namespace App\Application\Auth;

use AtelliTech\Hyperf\Auth\Contract\IdentityInterface;

final class UserIdentity implements IdentityInterface
{
    public function __construct(
        protected int|string $id,
        protected string $displayName,
        protected string $sub,
        protected string $avatar,
    ) {}

    public function getId(): int|string
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->displayName,
            'sub' => $this->sub,
            'avatar' => $this->avatar,
        ];
    }
}
```

---

## 🧩 4. Implement `IdentityProviderInterface`

Your identity provider should handle user lookup and JWT verification:

```php
<?php
declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\User\Repository\UserRepoInterface;
use AtelliTech\Hyperf\Auth\Contract\IdentityInterface;
use AtelliTech\Hyperf\Auth\Contract\IdentityProviderInterface;
use AtelliTech\Hyperf\Auth\Contract\JwtVerifierInterface;
use Hyperf\HttpMessage\Exception\UnauthorizedHttpException;
use Throwable;

class UserIdentityProvider implements IdentityProviderInterface
{
    public function __construct(
        protected UserRepoInterface $userRepo,
        protected JwtVerifierInterface $jwtVerifier,
    ) {}

    public function findIdentityById(int|string $id): ?IdentityInterface
    {
        $user = $this->userRepo->findOne($id);
        return $user ? UserIdentity::fromArray($user->toArray()) : null;
    }

    public function findIdentityBySub(int|string $sub): ?IdentityInterface
    {
        $user = $this->userRepo->find()->where('sub', $sub)->first();
        return $user ? UserIdentity::fromArray($user->toArray()) : null;
    }

    public function findIdentityByAccessToken(string $token): ?IdentityInterface
    {
        try {
            $claims = $this->jwtVerifier->verify($token);
        } catch (Throwable $e) {
            throw new UnauthorizedHttpException($e->getMessage(), $e->getCode(), $e);
        }

        if (! isset($claims['sub'])) {
            throw new UnauthorizedHttpException('JWT missing "sub" claim.');
        }

        return $this->findIdentityBySub($claims['sub']);
    }
}
```

---

## 🔧 5. Register Dependencies

In your `config/autoload/dependencies.php`:

```php
use AtelliTech\Hyperf\Auth\AuthManager;
use AtelliTech\Hyperf\Auth\WebUser;
use AtelliTech\Hyperf\Auth\Method\BearerAuth;
use AtelliTech\Hyperf\Auth\Method\QueryParamAuth;
use AtelliTech\Hyperf\Auth\Contract\IdentityProviderInterface;
use App\Application\Auth\UserIdentityProvider;

return [
    AuthManager::class => fn () => new AuthManager([
        new BearerAuth(),
        new QueryParamAuth('access_token'),
    ]),

    WebUser::class => fn () => new WebUser(),

    IdentityProviderInterface::class => UserIdentityProvider::class,
];
```

---

## 🧱 6. Usage Example

Inject `WebUser` in your controller to access the current authenticated identity:

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use AtelliTech\Hyperf\Auth\WebUser;
use Throwable;

class UserController extends AbstractController
{
    public function __construct(private WebUser $webUser) {}

    public function profile(): array
    {
        try {
            $identity = $this->webUser->getIdentity();
            return $identity ? $identity->toArray() : ['message' => 'Guest'];
        } catch (Throwable $e) {
            throw $e; // or handle custom error response
        }
    }
}
```

---

## 🔒 7. Protecting Routes

Attach the `UserAuthMiddleware` to any route or group you want to secure:

```php
use AtelliTech\Hyperf\Auth\UserAuthMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::addGroup('/v1', function () {
    Router::get('/me', [App\Controller\UserController::class, 'profile']);
}, ['middleware' => [UserAuthMiddleware::class]]);
```

---

## ✅ Result

When a valid JWT token is provided:

```bash
curl -H "Authorization: Bearer <jwt_token>" http://localhost:9501/v1/me
```

Response:

```json
{
  "id": 1,
  "display_name": "Eric Huang",
  "sub": "user-1234",
  "avatar": "https://example.com/avatar.jpg"
}
```

If the token is invalid or missing:

```json
{
  "status": 401,
  "error": "UnauthorizedHttpException",
  "message": "Invalid or expired token."
}
```

---

## 🧠 Summary

| Step | Description                                                                   |
| ---- | ----------------------------------------------------------------------------- |
| 1    | Install the package via Composer                                              |
| 2    | Publish & configure JWT settings                                              |
| 3    | Implement your own `IdentityInterface`                                        |
| 4    | Implement `IdentityProviderInterface` with JWT verification                   |
| 5    | Register dependencies (`AuthManager`, `WebUser`, `IdentityProviderInterface`) |
| 6    | Inject `WebUser` in controllers                                               |
| 7    | Protect routes with `UserAuthMiddleware`                                      |
