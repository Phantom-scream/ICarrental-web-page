<?php
class Auth {
    private $user_storage;
    private $user = NULL;

    public function __construct(IStorage $user_storage) {
        $this->user_storage = $user_storage;

        $admin = [
            'email' => 'admin@ikarrental.hu',
            'password' => password_hash('admin', PASSWORD_DEFAULT),
            'fullname' => 'Admin',
            'roles' => ['admin'],
            'id' => 'admin'
        ];

        if ($this->user_storage->findOne(['email' => $admin['email']]) === NULL) {
            $this->user_storage->add($admin);
        }

        if (isset($_SESSION["user"])) {
            $this->user = $_SESSION["user"];
        }
    }

    public function register($data) {
        $user = [
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'fullname' => $data['fullname'] ?? '',
            'roles'    => $data['roles'] ?? ['user'],
        ];
        return $this->user_storage->add($user);
    }

    public function user_exists($email) {
        $user = $this->user_storage->findOne(['email' => $email]);
        return !is_null($user);
    }

    public function authenticate($email, $password) {
        $user = $this->user_storage->findOne(['email' => $email]);
        if ($user && password_verify($password, $user["password"])) {
            return $user;
        }
        return NULL;
    }

    public function is_authenticated() {
        return !is_null($this->user);
    }

    public function authorize($roles = []) {
        if (!$this->is_authenticated()) {
            return FALSE;
        }
        foreach ($roles as $role) {
            if (in_array($role, $this->user["roles"])) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function login($user) {
        $this->user = $user;
        $_SESSION["user"] = $user;
    }

    public function logout() {
        $this->user = NULL;
        session_unset();
        session_destroy();
    }

    public function authenticated_user() {
        return $this->user;
    }
}
?>