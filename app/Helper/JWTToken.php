<?php
namespace App\Helper;
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTToken {
	
	// Generate JWT Token
	public static function createToken($userEmail, $userID):string {
		
		$key = env('JWT_KEY');
		$payload = [
			'iss' => "laravel-token",
			'iat' => time(),
			'exp' => time() + (60*60*24),
			'userEmail' => $userEmail,
			'userID' => $userID
		];

		$jwt = JWT::encode($payload, $key, 'HS256');
		return $jwt;

	}

	// Generate JWT Token
	public static function createTokenForSetPassword($userEmail):string {
		
		$key = env('JWT_KEY');
		$payload = [
			'iss' => "laravel-token",
			'iat' => time(),
			'exp' => time() + (60*20),
			'userEmail' => $userEmail,
			'userID' => '0'
		];

		$jwt = JWT::encode($payload, $key, 'HS256');
		return $jwt;

	}


	// Verify JWT Token
	public static function verifyToken($token):string|object {
		try{
			if(null === $token){
				return 'unauthorized';
			}else{
				$key = env('JWT_KEY');
				$decode = JWT::decode($token, new Key($key, 'HS256'));
				return $decode;
			}
		}catch(Exception $e){
			return "unauthorized";
		}
	}
}