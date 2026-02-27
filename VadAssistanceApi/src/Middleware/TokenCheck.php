<?php
// src/Middleware/TokenCheck.php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Cake\Http\Response;
use Cake\Core\Configure;


class TokenCheck implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $authHeader = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        
        // CAS 1 : Token totalement absent
        if (empty($token)) {
            return $this->errorResponse('Authentification requise', 401);
            }

        try {
            $JWTKEY = Configure::read('App.JWTApiToken'); // Récupère la clé secrète depuis la config
            // Décodage avec l'algorithme HS256
            $decoded = JWT::decode($token, new Key($JWTKEY, 'HS256'));

            // CAS 2 : Token valide -> On injecte tout le payload dans la requête
            $request = $request->withAttribute('user_data', $decoded);
            
        } catch (\Firebase\JWT\ExpiredException $e) {
            // CAS 3 : Token expiré
            return $this->errorResponse('Session expirée, veuillez vous reconnecter', 401);
        } catch (\Exception $e) {
            // CAS 4 : Token corrompu ou mauvaise clé
            return $this->errorResponse('Token invalide ou corrompu', 401);
        }

        return $handler->handle($request);
    }

    private function errorResponse(string $message, int $status): ResponseInterface {
        $response = new Response();
        return $response->withStatus($status)
            ->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'message' => $message]));
    }
}