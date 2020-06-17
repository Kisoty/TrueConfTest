<?php
declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Classes\User;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->post('/user/AddUser', function (Request $request, Response $response) {
        $request_body = $request->getParsedBody();
        if (empty($request_body['name']) || empty($request_body['phone'])) {
            $response->getBody()->write('Name and phone are required');
            return $response->withStatus(400, 'Name or phone are empty');
        } else {
            $user = new User();
            try {
                $newUserId = $user->addUser($request_body['name'], $request_body['phone']);
                $response_body = json_encode(['message' => 'User added','data' => ["Id" => $newUserId]]);
            } catch (Exception $e) {
                $response_body = json_encode(['error' => $e->getMessage()]);
            }
            $response->getBody()->write($response_body);
            return $response->withHeader('Content-type', 'application/json');
        }
    });

    $app->get('/user/GetUserById', function (Request $request, Response $response) {
        $request_params = $request->getQueryParams();
        $id = $request_params['id'];
        if (empty($id)) {
            $response->getBody()->write('Id is required');
            return $response->withStatus(400,'Empty param Id');
        } else {
            $user = new User();
            try {
                $user_data = $user->getUserById($id);
                $response_body = json_encode(['message' => 'User found','data' => $user_data]);
            } catch (Exception $e) {
                $response_body = json_encode(['error' => $e->getMessage()]);
            }
            $response->getBody()->write($response_body);
        }
        return $response->withHeader('Content-type','application/json');
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};
