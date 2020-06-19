
To run the application in development, you can run these commands 

```bash
cd [my-app-name]
composer start
```

Or you can use `docker-compose` to run the app with `docker`, so you can run these commands:
```bash
cd [my-app-name]
docker-compose up -d
```
After that, open `http://localhost:8080` in your browser.
___________________________________________________________________

Небольшая дока по API.

###1. GetUserById
Метод принимает GET-запрос с обязательным параметром id, являющимся целым числом.
Возвращает данные пользователя с заданным Id.
#####Пример запроса
>http://localhost:8080/user/GetUserById?id=1
#####Пример успешного ответа
```json
{
     "message": "User found",
     "data": {
         "id": 1,
         "name": "ayaya",
         "phone": "77111111111"
     }
 }
```

###2. GetUsers
Метод принимает GET-запрос без параметров.
Вьозвращает список пользователей.
#####Пример запроса
>http://localhost:8080/user/GetUsers
#####Пример успешного ответа
```json
{
    "message": "Users found",
    "data": [
        {
            "id": 1,
            "name": "ayaya",
            "phone": "77111111111"
        },
        {
            "id": 2,
            "name": "ayaya",
            "phone": "7111111111"
        },
        {
            "id": 3,
            "name": "ayaya",
            "phone": "11111111111"
        }
    ]
}
```
###3. AddUser
Метод принимает POST-запрос. Тело запроса: json с двумя обязательными полями: name и phone.
Создает нового пользователя, возвращает данные пользователя.
#####Пример запроса
>http://localhost:8080/user/AddUser
```json
{
	"name":"oyoyo",
	"phone":"77111111216"
}
```
#####Пример успешного ответа
```json
{
    "data": {
        "id": 8,
        "name": "oyoyo",
        "phone": "77111111216"
    }
}
```
###4. DeleteUserById
Метод принимает DELETE-запрос с обязательным параметром id, являющимся целым числом.
Удаляет пользователя с данным id, о чем радостно оповещает.
#####Пример запроса
>http://localhost:8080/user/DeleteUserById?id=6
#####Пример успешного ответа
```json
{
    "message": "User deleted"
}
```

###5. UpdateUserById
Метод принимает PUT-запрос. Тело запроса: json с обязательным полем id, являющимся целым числом.
Можно приправить полями phone и name по вкусу. 
Обновляет поля phone/name, присваивая им переданные значения.
Возвращает новые данные пользователя.
#####Пример запроса
>http://localhost:8080/user/UpdateUserById
```json
 {
     "data": {
         "id": "7",
         "name": "oyoyo",
         "phone": "77111111545"
     }
 }
 ```
#####Пример успешного ответа
```json
{
    "data": {
        "id": "7",
        "name": "oyoyo",
        "phone": "77111111545"
    }
}
```
###6. GetUserIdByPhone
Метод принимает Get запрос с параметром phone, являющимся валидным номером телефона. 
Можете поискать невалидный номер, который все равно пройдет проверку, но это непросто.
#####Пример запроса
>http://localhost:8080/user/GetUserIdByPhone?phone=77111111111
#####Пример успешного ответа
```json
{
    "message": "User found",
    "data": {
        "id": 1
    }
}
```
До работы над этим тестовым заданием я был совершенно не знаком с этим фреймворком,
посему я явно не использовал большинство его возможностей, ибо за 3 дня хоть сколько-нибудь
достойно изучить его явно не успел. ¯\_(ツ)_/¯

Также непосредственно в коде есть несколько комментариев, 
которые я бы с радостью обсудил на собеседовании :).