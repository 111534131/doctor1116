<?php
// NOTE: For production, it's highly recommended to use a trusted library like firebase/php-jwt.
// This is a basic implementation for compatibility with the existing C# backend.

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function generate_jwt($user, $key, $issuer, $audience) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

    $claims = [
        'sub' => $user['Id'],
        'email' => $user['Email'],
        'name' => $user['Username'],
        'jti' => uniqid(),
        // This matches the C# claim type for Role
        'http://schemas.microsoft.com/ws/2008/06/identity/claims/role' => $user['Role'], 
        'iss' => $issuer,
        'aud' => $audience,
        'iat' => time(),
        'exp' => time() + (120 * 60) // 120 minutes expiry
    ];
    $payload = json_encode($claims);

    $base64UrlHeader = base64url_encode($header);
    $base64UrlPayload = base64url_encode($payload);

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);
    $base64UrlSignature = base64url_encode($signature);

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function validate_jwt($jwt, $key) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        throw new Exception('Invalid token format');
    }
    list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

    $signature = base64url_decode($base64UrlSignature);
    $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);

    if (!hash_equals($expectedSignature, $signature)) {
        throw new Exception('Invalid signature');
    }

    $payload = json_decode(base64url_decode($base64UrlPayload));

    if ($payload === null) {
        throw new Exception('Invalid payload');
    }

    if ($payload->exp < time()) {
        throw new Exception('Expired token');
    }
    
    // The C# JWT handler maps the long claim name to a simple 'role' property in the ClaimsPrincipal.
    // The PHP code needs to do this manually.
    $role_claim = 'http://schemas.microsoft.com/ws/2008/06/identity/claims/role';
    if (isset($payload->$role_claim)) {
        $payload->role = $payload->$role_claim;
    } else {
        $payload->role = 'User'; // Default role if not present
    }

    return $payload;
}
?>
