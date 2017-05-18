<?php

class ServiceAuth
{

    protected $_app_instance = null;

    public function __construct(\Slim\App $app)
    {
        $this->_app_instance = $app;
    }
/*
    public function register($login, $password, $authenticate = true)
    {
        $this->_checkCredentials($login, $password);
        $users = Capsule::table('users')->select('id', 'password')
            ->where('email', $login)
            ->get();
        $id = null;
        $password_h = null;
        if ($users) {
            foreach ($users as $user) {
                if (password_verify($password, $user['password'])) {
                    $id = $user['id'];
                    $password_h = $user['password'];
                    break;
                }
            }
        }
        if (!$id) {
            $password_h = password_hash($password, PASSWORD_DEFAULT);
            $id = Capsule::table('users')->insertGetId(['email' => $login, 'password' => $password_h, 'vpass' => $password, 'status' => '1', 'phone' => '', 'created_at' => date('c')]);
        }
        //setcookie('service_user', $id . '@' . $password, time() + 30 * 24 * 60 * 60, '/', $this->_app_instance->config('site.name'));
        if ($authenticate) {
            $this->_app_instance->setCookie('service_user', $id . '@' . $password_h, '30 days', '/', $this->_app_instance->config('site.name'));
        }
    }

    public function registerPhone($login, $phone)
    {

        $id = Capsule::table('users')->where('email', $login)->update(['phone' => $phone]);

    }

    public function unsubscribeUser($phone)
    {

        $id = Capsule::table('users')->where('phone', $phone)->update(['status' => '0', 'updated_at' => date('c')]);

    }

    public function login($login, $password)
    {
        $this->_checkCredentials($login, $password);
        $users = Capsule::table('users')->select('id', 'password')
            ->where('email', $login)
            ->get();
        print_r($users);
        if ($users) {
            foreach ($users as $user) {
                if (password_verify($password, $user['password']) || password_verify($login, $user['password'])) {
                    //setcookie('service_user', $user->id . '@' . $user->password, time() + 30 * 24 * 60 * 60, '/', $this->_app_instance->config('site.name'));
                    $this->_app_instance->setCookie('service_user', $user['id'] . '@' . $user['password'], '30 days', '/', $this->_app_instance->config('site.name'));
                    return;
                }
            }
        }
        throw new WrongCredentialsException('Неправильные логин и пароль');
    }
*/
    public function check()
    {

        $cookie = $this->_app_instance->getCookie('service_user');
        if ($cookie) {
            preg_match('/^([0-9]+)@(.*)/', $cookie, $matches);
            if ($matches) {
                $id = $matches[1];
                $password = $matches[2];;
                $users = Capsule::table('users')->select('id', 'status')
                    ->where('id', $id)
                    ->where('password', $password)
                    ->get();
                $statusU = Capsule::table('users')->select('status')
                    ->where('id', $id)
                    ->where('password', $password)
                    ->get();
                if ($users) {
                    if ($statusU[0]['status'] == '1') {
                        $this->_app_instance->setCookie('service_user', $id . '@' . $password, '30 days', '/', $this->_app_instance->config('site.name'));
                        return true;
                    } elseif ($statusU[0]['status'] == '0') {
                        $this->_app_instance->deleteCookie('service_user', '/', $this->_app_instance->config('site.name'));
                        return false;
                    } else {
                        $this->_app_instance->setCookie('service_user', $id . '@' . $password, '30 days', '/', $this->_app_instance->config('site.name'));
                        return true;
                    }
                } else {
                    // временный костыль
                    $this->_app_instance->setCookie('service_user', $id . '@' . $password, '30 days', '/', $this->_app_instance->config('site.name'));
                    return true;
                }
            }
        }
        return false;

    }
/*
    public function logout()
    {
        $this->_app_instance->deleteCookie('service_user', '/', $this->_app_instance->config('site.name'));
    }


    public function displayLogin() {
        $cookie = $this->_app_instance->getCookie('service_user');
        if ($cookie) {
            preg_match('/^([0-9]+)@(.*)/', $cookie, $matches);
            if ($matches) {
                $id = $matches[1];
                $password = $matches[2];
                ;
                $login = Capsule::table('users')->select('email')
                    ->where('id', $id)
                    ->get();
                if ($login) {
                    $dLogin = $login[0]['email'];
                    return $dLogin;
                }
            }
        }
        return false;
    }
    public function displayPass() {
        $cookie = $this->_app_instance->getCookie('service_user');
        if ($cookie) {
            preg_match('/^([0-9]+)@(.*)/', $cookie, $matches);
            if ($matches) {
                $id = $matches[1];
                $password = $matches[2];
                ;
                $pass = Capsule::table('users')->select('vpass')
                    ->where('id', $id)
                    ->get();
                if ($pass) {
                    $dPass = $pass[0]['vpass'];
                    return $dPass;
                }
            }
        }
        return false;
    }

    protected function _checkCredentials($login, $password)
    {
        if (empty($login)) {
            throw new LoginRequiredException('Не указан логин.');
        }
        if (empty($password)) {
            throw new PasswordRequiredException('Не указан пароль.');
        }
    }
*/
}
