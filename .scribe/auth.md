# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer Bearer {YOUR_AUTH_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

This API uses Laravel Sanctum for authentication. To obtain a token, you need to authenticate through the appropriate endpoints. Include the token in the Authorization header as `Bearer {token}` for authenticated requests.
