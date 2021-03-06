<?php

class User {

    private $id;
    private $email;
    private $hash;
    private $pdo;


    /**
     * sets class variables
     *
     * @param PDO $pdo database connection
     */
    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    /**
     * validates that given email is valid
     *
     * @param STRING $email given email to check
     *
     * @return BOOLEAN
     *
     * @throws Exception
     */
    private function validateEmail($email) {

        // Remove all illegal characters from email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // Validate e-mail
        if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new Exception('not a valid email address');
        }

        return true;
    }

    /**
     * sets the session data and adds random validation string to database
     *
     * @param STRING $email email to check against database
     * @param STRING $password password to check against database
     *
     * @return BOOLEAN
     *
     * @throws Exception
     */
    public function login($email, $password) {

        if($this->validateEmail($email) && $this->validateDetails($email, $password)) {
            $token = sha1(time());

            //set all data used to validate / display
            $_SESSION['userAuth'] = $token;
            $_SESSION['id'] = $this->id;
            $_SESSION['email'] = $email;

            $sql = "UPDATE `users` SET `validationString` = :token WHERE `id` = " . $this->id . ";";
            $query = $this->pdo->prepare($sql);
            return $query->execute([':token'=>$token]);
        } else {
            throw new Exception('Invalid Login');
        }
    }

    /**
     * updates user email in database and $_SESSION
     *
     * @param STRING $newEmail email to add to database
     *
     * @throws Exception
     */
    public function changeEmail($newEmail){

        if($this->validateEmail($newEmail)){
            try {
                $sql = "UPDATE `users` SET `email` = :email WHERE `id` = " . $this->id . ";";
                $query = $this->pdo->prepare($sql);
                $query->execute([':email'=>$newEmail]);

                $_SESSION['email'] = $newEmail;
            } catch(Exception $e) {
                throw new Exception('email already exist');
            }

        }


    }

    /**
     * updates user password in database
     *
     * @param STRING $password password to add to database
     */
    public function changePassword($password){

        $newPassword = $this->hash . $password;
        $newPassword = sha1($newPassword);

        $sql = "UPDATE `users` SET `password` = :password WHERE `id` = " . $this->id . ";";
        $query = $this->pdo->prepare($sql);
        $query->execute([':password'=>$newPassword]);

    }

    /**
     * validates user login details:
     * if email and password match database then sets $loggedIn to TRUE and updates class variables
     *
     * @param STRING $email user email
     * @param STRING $password user password
     *
     * @return BOOLEAN returns if login is successful
     *
     * @throws Exception
     */
    public function validateDetails($email, $password){

        if(!$this->validateEmail($email)) {
            throw new Exception("not valid email");
        }

        $sql = "SELECT * FROM `users` WHERE `email` = :email;";
        $query = $this->pdo->prepare($sql);
        $query->execute([':email'=>$email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if(empty($user)) {
            throw new Exception("user does not exist");
        }

        $encryptPass = $user['hash'] . $password;
        $encryptPass = sha1($encryptPass);

        if($user['password'] != $encryptPass) {
            throw new Exception("incorrect email and password combination");
        } else {
            return $this->setUserDetails($user);
        }

    }

    /**
     * validates that the session data matches up with the data in the database
     *
     * @param STRING $token validation string to check against database
     * @param STRING $id id of user to check validation string against
     *
     * @returns BOOLEAN
     *
     * @throws Exception
     */
    public function validateToken($token, $id) {
        $sql = "SELECT * FROM `users` WHERE `id` = :id;";
        $query = $this->pdo->prepare($sql);
        $query->execute([':id'=>$id]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if($token != $user['validationString']) {
            throw new Exception('error validating user');
        }
        
        return $this->setUserDetails($user);

    }

    /**
     * updates class variables
     *
     * @param ARRAY $user array containing all user details
     *
     * @return BOOLEAN
     */
    public function setUserDetails($user){
        $this->id = $user['id'];
        $this->email = $user['email'];
        $this->hash = $user['hash'];
        return true;
    }

}