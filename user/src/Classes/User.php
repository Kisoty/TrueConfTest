<?php


namespace App\Classes;


class User
{
    /**
     * Json file with user data
     * @var string
     */
    private $json = __DIR__ . '/../../users.json';

    /**
     * @param string $name
     * @param string $phone
     * @return array
     * @throws \ErrorException
     */
    public function addUser(string $name, string $phone) : array
    {
        if (!preg_match('/(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})[-. ]*(\d{4})/',$phone))
            throw new \InvalidArgumentException('Insert correct phone number');

        $start = null;
        $end = null;
        $inner_count = 0;
        $buff_arr = [];

        try {
            $f = fopen($this->json,'rb');
        } catch (\Exception $e) {
            throw new \ErrorException($e->getMessage());
        }

        foreach ($this->fileReadGen($f,1) as $value) {
            if ($value === '{') {
                if ($inner_count === 0)
                    $start = ftell($f)-1;
                else
                    $inner_count++;
            }
            if ($value === '}') {
                if ($inner_count === 0)
                    $end = ftell($f);
                else $inner_count--;
            }
            if ($start && $end){
                fseek($f,$start);
                $buff_arr= json_decode(fread($f,$end-$start));
                if ($buff_arr->phone == $phone) {
                    fclose($f);
                    throw new \Exception('User with this phone number already exists');
                }
                $start = null;
                $end = null;
                $inner_count = 0;
            }
        }
        $f = fopen($this->json,'rb+');
        fseek($f,-4096,SEEK_END);
        $chunk =  fread($f,4096);
        $lastUser = json_decode(substr(strrchr($chunk,'{'),0,strlen(strrchr($chunk,'{'))-1));
        $newUserId = $lastUser->id + 1;
        $newUser = ['id' => $newUserId, 'name' => $name, 'phone' => $phone];
        $newUserJson = ','.json_encode($newUser).']';
        fseek($f,-1,SEEK_END);
        $write = fwrite($f,$newUserJson);
        fclose($f);
        if (!$write) {
            throw new \ErrorException('Cannot add user');
        }
        return $newUser;
    }

    /**
     * @param string $id
     * @return object
     * @throws \ErrorException
     */
    public function getUserById (string $id) : object
    {
        try {
            $f = fopen($this->json, 'rb');
        } catch (\Exception $e) {
            throw new \ErrorException($e->getMessage());
        }

        $buff_arr = [];
        $start = null;
        $end = null;
        $inner_count = 0;

        foreach ($this->fileReadGen($f,1) as $value) {
            if ($value === '{') {
                if ($inner_count === 0)
                    $start = ftell($f)-1;
                else
                    $inner_count++;
            }
            if ($value === '}') {
                if ($inner_count === 0)
                    $end = ftell($f);
                else $inner_count--;
            }
            if ($start && $end){
                fseek($f,$start);
                $buff_arr= json_decode(fread($f,$end-$start));
                if ($buff_arr->id == $id) {
                    fclose($f);
                    return $buff_arr;
                }
                $start = null;
                $end = null;
                $inner_count = 0;
            }
        }
        throw new \Exception('User with given Id doesn\'t exist');
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    public function getAllUsers () : array
    {
        //К сожалению, придется считывать весь файл
        try {
            $content = json_decode(file_get_contents($this->json));
            if ($content === false) {
                throw new \ErrorException('Couldn\'t read data from file');
            }
        } catch (\Exception $e) {
            throw new \ErrorException($e->getMessage());
        }
        return $content;
    }

    /**
     * @param string $id
     * @return bool
     * @throws \ErrorException
     */
    public function deleteUserById (string $id) : bool
    {
        //К сожалению php не предоставляет возможности редачить файлы на ходу, придется грузить весь файл.
        //Это явно можно сделать системными утилитами, но я, к своему величайшему стыду, пока работаю с винды,
        //а писать что-то серверное под винду...
        try {
            $user_list = json_decode(file_get_contents($this->json));
        } catch (\Exception $e) {
            throw new \ErrorException($e->getMessage());
        }

        foreach ($user_list as $key => $user) {
            if ($user->id == $id) {
                unset($user_list[$key]);
                $user_list = array_values($user_list);
                try {
                    file_put_contents($this->json, json_encode($user_list));
                } catch (\Exception $e) {
                    throw new \ErrorException($e->getMessage());
                }
                return true;
            }
        }
        throw new \Exception('User with given Id doesn\'t exist');
    }

    /**
     * @param array $newUserData
     * @return array
     * @throws \ErrorException
     */
    public function updateUserById (array $newUserData) : array
    {
        if (!empty($newUserData['phone'])) {
            $phone = $newUserData['phone'];
            if (!preg_match('/(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})[-. ]*(\d{4})/', $phone))
                throw new \InvalidArgumentException('Insert correct phone number');
        } else {
            unset($newUserData['phone']);
        }

        //Тут и чуть выше неочевидный момент: если поле приходит пустым, перезаписывать ли его, молча пропускать либо кидать эксепшн.
        if (empty($newUserData['name']))
            unset($newUserData['name']);

        //Аналогично с deleteUser
        try {
            $user_list = json_decode(file_get_contents($this->json));
        } catch (\Exception $e) {
            throw new \ErrorException($e->getMessage());
        }

        foreach ($user_list as $key => &$user) {
            if ($user->id == $newUserData['id']) {
                ob_start();
                foreach ($newUserData as $k => $param) {
                    $user->{$k} = $param;
                }
                try {
                    file_put_contents($this->json, json_encode($user_list));
                } catch (\Exception $e) {
                    throw new \ErrorException($e->getMessage());
                }
                return $newUserData;
            }
        }
        throw new \Exception('User with given Id doesn\'t exist');
    }

    /**
     * @param string $phone
     * @return int
     * @throws \ErrorException
     */
    public function getUserIdByPhone (string $phone) : int
    {
        if (!preg_match('/(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})[-. ]*(\d{4})/',$phone))
            throw new \InvalidArgumentException('Insert correct phone number');

        try {
            $f = fopen($this->json, 'rb');
        } catch (\Exception $e) {
            throw new \ErrorException($e->getMessage());
        }

        $buff_arr = [];
        $start = null;
        $end = null;
        $inner_count = 0;

        foreach ($this->fileReadGen($f,1) as $value) {
            if ($value === '{') {
                if ($inner_count === 0)
                    $start = ftell($f)-1;
                else
                    $inner_count++;
            }
            if ($value === '}') {
                if ($inner_count === 0)
                    $end = ftell($f);
                else $inner_count--;
            }
            if ($start && $end){
                fseek($f,$start);
                $buff_arr= json_decode(fread($f,$end-$start));
                if ($buff_arr->phone == $phone) {
                    fclose($f);
                    return $buff_arr->id;
                }
                $start = null;
                $end = null;
                $inner_count = 0;
            }
        }
        throw new \Exception('User with given phone number doesn\'t exist');
    }

    /**
     * @param $file_resource
     * @param int $chunk
     * @return \Generator
     */
    private function fileReadGen($file_resource, int $chunk)
    {
        for ($i = 0; $i < filesize($this->json); $i++) {
            yield fread($file_resource, $chunk);
        }
    }
}