<?php

class User
{
    protected $username;
    protected $password;
    protected $passwordVerify;

    protected $first_name;
    protected $last_name;
    protected $email;
    protected $emailPass;

    public function __construct($db)
    {
        $this->db = $db;
    }
    function rand_string( $length ) {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars),0,$length);
    }
    public function register()
    {

        $date = date('Y/m/d h:i:s');
        $error = 0;
        $new_date;
        if (isset($_POST['submit']))
        {
            $this->first_name = $_POST['first_name'] ?? 'undefined';
            $this->last_name = $_POST['last_name'] ?? 'undefined';
            $this->email = $_POST['email'] ?? 'undefined';
            $password = $_POST['password'] ?? 'undefined';
            $birth_date = strtotime($_POST['birth_date']) ?? 'undefined';
            $phone_number = $_POST['phone_number'] ?? 'undefined';
            $role_id = $_POST['role_id'] ?? 'undefined';
            $verifyPassword = $_POST[ 'verifyPassword'] ?? 'undefined';
            $hashed_password;
            
            if ($birth_date) {
                $new_date = date('Y-m-d', $birth_date);
            } else {
                echo 'Invalid Date: ' . $_POST['dateFrom'];
                $error++;
            }
            if($role_id == 'leraar')
            {
                if ($password === $verifyPassword)
                {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                } else
                {
                    $error = 1;
                    echo 'Wachtwoorden zijn niet gelijk!';
                }
            }else{
                $this->emailPass =  $this->rand_string(12);
                $hashed_password = password_hash($this->emailPass, PASSWORD_DEFAULT);
                $this->sendEmail();
            }

            if ($error === 0)
            {
                $sql = "INSERT INTO users (role_id,first_name, last_name, email,password,birth_date,phone_number,created_at) VALUES (?,?,?,?,?,?,?,?)";
                $stmt= $this->db->prepare($sql);
                $stmt->execute([$role_id, $this->first_name, $this->last_name,$this->email,$hashed_password,$new_date,$phone_number,$date]);
                $_SESSION['success'] = 'Er is een email verzonden met uw wachtwoord';
            }
        }
    }
     public function login()
    {
        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        }
        $token = $_SESSION['token'];
        
        if (!empty($_POST['token'])) {
            if (hash_equals($_SESSION['token'], $_POST['token'])) {
                if(isset($_POST['submit'])){

                    $this->username = trim($_POST['email']);
                    $this->password = trim($_POST['password']);
                    $sql = "SELECT * FROM users WHERE email =?";

                    try {
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute(array($_POST['email']));
                        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                        if(isset($rows[0])) {
                            if (password_verify($_POST['password'], $rows[0]['password'])) {
                                $this->loginError = "";
                                $_SESSION['session_id'] = session_id();
                                echo "<script type='text/javascript'>window.location.href = \"/dashboard\";</script>";
                                return true;
                            }else {
                                $_SESSION['loginError'] = 'Email of Wachtwoord is onjuist!';
                            }
                        }
                        else{
                            $_SESSION['loginError'] = 'Email is onjuist';
                            $this->loginError = "Email of wachtwoord is onjuist!";
                        }
                    } catch (PDOException $e) {
                        $_SESSION['loginError'] = $e;

                    }
 
                } else
                {
                    $_SESSION['loginError'] = 'Email of Wachtwoord is onjuist!3';

                    $this->loginError = "Email of wachtwoord is onjuist!";
                }
            } else {
                echo "kan niet";
            }
        }
    }

   
}