<?php

require_once __DIR__ . '/../services/AuthService.class.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

Flight::set('auth_service', new AuthService());

Flight::group('/auth', function() {
    Flight::route('POST /login', function() {
        $payload = Flight::request()->data->getData();

        $auth_service = Flight::get('auth_service');
        $result = $auth_service->authenticate($payload['email'], $payload['password']);

        if(!$result) {
            Flight::halt(401, "Invalid username or password");
        }

        Flight::json($result);
    });

    Flight::route('POST /logout', function() {
        try {
            $token = Flight::request()->getHeader("Authorization");
            if(!$token) {
                Flight::halt(401, "Missing authentication header");
            }

            $decoded_token = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));

            Flight::json([
                'jwt_decoded' => $decoded_token,
                'user' => $decoded_token->user
            ]);
        } catch (\Exception $e) {
            Flight::halt(401, $e->getMessage());
        }
    });
});
