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
                $newUser = $user->addUser($request_body['name'], $request_body['phone']);
                $response_body = json_encode(['data' => $newUser]);
                $response->getBody()->write($response_body);
                return $response->withHeader('Content-type', 'application/json')->withStatus(201,'User created');
            } catch (InvalidArgumentException $e) {
                $response_body = json_encode(['error' => $e->getMessage()]);
                $response->getBody()->write($response_body);
                return $response->withHeader('Content-type', 'application/json')->withStatus(400,'Wrong phone format');
            } catch (ErrorException $e) {
                $response_body = json_encode(['error' => $e->getMessage()]);
                $response->getBody()->write($response_body);
                return $response->withHeader('Content-type', 'application/json')->withStatus(500,'Server internal error');
            } catch (Exception $e) {
                $response_body = json_encode(['error' => $e->getMessage()]);
                $response->getBody()->write($response_body);
                return $response->withHeader('Content-type', 'application/json');
            }
        }
    });

    $app->get('/user/GetUserById', function (Request $request, Response $response) {
        $request_params = $request->getQueryParams();
        if (empty($request_params['id']) || !is_numeric($request_params['id'])) {
            $response_body = json_encode(['error' => 'Parameter Id should be a number']);
            $response->getBody()->write($response_body);
            return $response->withStatus(400,'Id is not a number')->withHeader('Content-type', 'application/json');
        } else {
            $user = new User();
            try {
                $id = $request_params['id'];
                $user_data = $user->getUserById($id);
                $response_body = json_encode(['message' => 'User found','data' => $user_data]);
                $response->getBody()->write($response_body);
                return $response->withHeader('Content-type','application/json');
            } catch (ErrorException $e) {
                $response_body = json_encode(['error' => $e->getMessage()]);
                $response->getBody()->write($response_body);
                return $response->withStatus(500,'Server internal error')->withHeader('Content-type', 'application/json');
            } catch (Exception $e) {
                $response_body = json_encode(['error' => $e->getMessage()]);
                $response->getBody()->write($response_body);
                //Не знаю, с каким кодом вернуть лучше, 204 не вернет контента,
                //а какое-то информативное сообщение хочется вернуть. Поэтому пока 200 (дефолт).
                //Возможно, совершенно нормально передать информацию в статусе, просто в тех API,
                //с которыми я имел дело, даже в подобных случаях возвращалось тело ответа с ошибкой.
                return $response->withHeader('Content-type','application/json');
            }
        }
    });

    $app->get('/user/GetUsers', function (Request $request, Response $response) {
        $user = new User();
        try {
            $user_list = $user->getAllUsers();
            $response_body = json_encode(['message' => 'Users found', 'data' => $user_list]);
            $response->getBody()->write($response_body);
            return $response->withHeader('Content-type', 'application/json');
        } catch (ErrorException $e) {
            $response_body = json_encode(['error' => $e->getMessage()]);
            $response->getBody()->write($response_body);
            return $response->withHeader('Content-type', 'application/json')->withStatus(500,'Server internal error');
        }
    });

    $app->delete('/user/DeleteUserById', function (Request $request, Response $response) {
       $request_params = $request->getQueryParams();
       $id = $request_params['id'];
       if (empty($id) || !is_numeric($id)) {
           $response_body = json_encode(['error' => 'Parameter Id should be a number']);
           $response->getBody()->write($response_body);
           return $response->withStatus(400,'Id is not a number')->withHeader('Content-type', 'application/json');
       } else {
           $user = new User();
           try {
               $user->deleteUserById($id);
               $response->getBody()->write(json_encode(['message' => 'User deleted']));
               return $response->withHeader('Content-Type','application/json');
           } catch (ErrorException $e) {
               $response_body = json_encode(['error' => $e->getMessage()]);
               $response->getBody()->write($response_body);
               return $response->withHeader('Content-type', 'application/json')->withStatus(500,'Server internal error');
           } catch (Exception $e) {
               $response_body = json_encode(['error' => $e->getMessage()]);
               $response->getBody()->write($response_body);
               //Аналогично с GetUserById
               return $response->withHeader('Content-type', 'application/json');
           }
       }
    });

    $app->put('/user/UpdateUserById', function (Request $request, Response $response) {
        $newUserDataRequest = $request->getParsedBody();
        if (empty($newUserDataRequest['id']) || !is_numeric($newUserDataRequest['id'])) {
            $response_body = json_encode(['error' => 'Parameter Id should be a number']);
            $response->getBody()->write($response_body);
            return $response->withStatus(400,'Id is not a number')->withHeader('Content-type', 'application/json');
        }
        $user = new User();
        try {
            $newData = $user->updateUserById($newUserDataRequest);
            $response_body = json_encode(['data' => $newData]);
            $response->getBody()->write($response_body);
            return $response->withHeader('Content-type', 'application/json')->withStatus(200,'User data updated');
        } catch (ErrorException $e) {
            $response_body = json_encode(['error' => $e->getMessage()]);
            $response->getBody()->write($response_body);
            return $response->withHeader('Content-type', 'application/json')->withStatus(500,'Server internal error');
        } catch (Exception $e) {
            $response_body = json_encode(['error' => $e->getMessage()]);
            $response->getBody()->write($response_body);
            //Аналогично с GetUserById и DeleteUserById
            return $response->withHeader('Content-type', 'application/json');
        }
    });

    $app->get('/user/GetUserIdByPhone', function (Request $request, Response $response) {
        $request_params = $request->getQueryParams();
        if (empty($request_params['phone'])) {
            $response_body = json_encode(['error' => 'Parameter phone is required']);
            $response->getBody()->write($response_body);
            return $response->withStatus(400,'Missing phone parameter')->withHeader('Content-type', 'application/json');
        } else {
            $user = new User();
            try {
                $phone = $request_params['phone'];
                $user_id = $user->getUserIdByPhone($phone);
                $response_body = json_encode(['message' => 'User found','data' => ['id' => $user_id]]);
                $response->getBody()->write($response_body);
                return $response->withHeader('Content-type','application/json');
            } catch (ErrorException $e) {
                $response_body = json_encode(['error' => $e->getMessage()]);
                $response->getBody()->write($response_body);
                return $response->withStatus(500,'Server internal error')->withHeader('Content-type', 'application/json');
            } catch (Exception $e) {
                $response_body = json_encode(['error' => $e->getMessage()]);
                $response->getBody()->write($response_body);
                //Аналогично, что и GetUserById, DeleteUserById и UpdateUserById
                return $response->withHeader('Content-type','application/json');
            }
        }
    });

};
