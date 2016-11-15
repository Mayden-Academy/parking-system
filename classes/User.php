<?php

class User {

    private $loggedIn;
    private $id;
    private $pdo;


    /**
     * sets class variables
     *
     * @param PDO $pdo database connection
     */
    public function __construct(PDO $pdo){
        $this->loggedIn = FALSE;
        $this->pdo = $pdo;
    }

    /**
     * validates user login details:
     * if email and password match database then sets $loggedIn to TRUE
     *
     * @param STRING $email user email
     * @param STRING $password user password
     *
     * @throws Exception
     */
    function login($email, $password) {

        $sql = "SELECT * FROM `users` WHERE `email` = :email;";
        $query = $this->pdo->prepare($sql);
        $query->execute([':email'=>$email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        $encryptPass = $user["id"] . $password;
        $encryptPass = sha1($encryptPass);

        if(empty($user)) {
            throw new Exception("user does not exist");
        } elseif($user["password"] != $encryptPass) {
            throw new Exception("incorrect email and password combination");
        } else {
            $this->loggedIn = TRUE;
            $this->id = $user['id'];
        }
    }

    public function logOut(){
        $this->loggedIn = FALSE;
    }

    public function getLoggedIn(){
        return $this->loggedIn;
    }

    /**
     * updates user email in database
     *
     * @param STRING $newEmail email to add to database
     */
    public function changeEmail($newEmail){

        $sql = "UPDATE `users` SET `email` = :email WHERE `id` = " . $this->id . ";";
        $query = $this->pdo->prepare($sql);
        $query->execute([':email'=>$newEmail]);

    }

    /**
     * updates user password in database
     *
     * @param STRING $newPassword password to add to database
     */
    public function changePassword($newPassword){

        $newPassword = $this->id . $newPassword;
        $newPassword = sha1($newPassword);

        $sql = "UPDATE `users` SET `password` = :password WHERE `id` = " . $this->id . ";";
        $query = $this->pdo->prepare($sql);
        $query->execute([':password'=>$newPassword]);

    }
}