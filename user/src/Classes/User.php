<?php


namespace App\Classes;


class User
{
    private $json = __DIR__ . '/../../users.json';

    public function addUser(string $name, string $phone) : int
    {
        if (!preg_match('/(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})[-. ]*(\d{4})/',$phone))
            throw new \Exception('Wrong phone format');

        $start = null;
        $end = null;
        $inner_count = 0;
        $f = fopen($this->json,'rb');
        $buff_arr = [];

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
        $newUser = ','.json_encode(['id' => $newUserId, 'name' => $name, 'phone' => $phone]).']';
        fseek($f,-1,SEEK_END);
        $write = fwrite($f,$newUser);
        fclose($f);
        if (!$write) {
            throw new \Exception('Cannot add user');
        }
        return $newUserId;
    }

    public function getUserById (string $id) : object
    {
        if (!is_numeric($id))
            throw new \Exception('Id should be a number');
        $f = fopen($this->json,'rb');
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
        throw new \Exception('User with given id doesn\'t exist');
    }

    private function fileReadGen($file_resource,int $chunk)
    {
        for ($i = 0; $i < filesize($this->json); $i++) {
            yield fread($file_resource, $chunk);
        }
    }
}