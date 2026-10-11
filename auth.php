<?php
require_once __DIR__.'/config.php';

class Auth{
    private $db;
    public function __construct(){$this->db=Database::getInstance()->getConnection();}
    
    public function login($username,$password){
        $stmt=$this->db->prepare("SELECT id,username,password_hash,role,full_name FROM users WHERE username=? LIMIT 1");
        $stmt->execute([$username]);
        $user=$stmt->fetch();
        if($user&&password_verify($password,$user['password_hash'])){
            $token=bin2hex(random_bytes(32));
            $expires=date('Y-m-d H:i:s',strtotime('+24 hours'));
            $stmt=$this->db->prepare("INSERT INTO sessions(user_id,token,expires_at)VALUES(?,?,?)");
            $stmt->execute([$user['id'],$token,$expires]);
            setcookie('session_token',$token,['expires'=>time()+86400,'path'=>'/','httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']),'samesite'=>'Lax']);
            $_SESSION['user_id']=$user['id'];$_SESSION['role']=$user['role'];$_SESSION['username']=$user['username'];$_SESSION['full_name']=$user['full_name'];
            return['success'=>true,'user'=>$user];
        }
        return['success'=>false,'message'=>'Неверный логин или пароль'];
    }
    
    public function logout(){
        if(isset($_COOKIE['session_token'])){$stmt=$this->db->prepare("DELETE FROM sessions WHERE token=?");$stmt->execute([$_COOKIE['session_token']]);}
        setcookie('session_token','',['expires'=>time()-3600,'path'=>'/','httponly'=>true]);
        $_SESSION=[];return true;
    }
    
    public function checkAuth(){
        if(isset($_SESSION['user_id']))return true;
        if(isset($_COOKIE['session_token'])){
            $stmt=$this->db->prepare("SELECT s.*,u.id as user_id,u.role,u.username,u.full_name FROM sessions s JOIN users u ON s.user_id=u.id WHERE s.token=? AND s.expires_at>NOW() LIMIT 1");
            $stmt->execute([$_COOKIE['session_token']]);
            $s=$stmt->fetch();
            if($s){$_SESSION['user_id']=$s['user_id'];$_SESSION['role']=$s['role'];$_SESSION['username']=$s['username'];$_SESSION['full_name']=$s['full_name'];return true;}
        }
        return false;
    }
    
    public function getCurrentUser(){
        if(!$this->checkAuth())return null;
        $stmt=$this->db->prepare("SELECT id,username,role,subject_id,class_id,full_name,email FROM users WHERE id=?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    
    public function hasRole($roles){if(!is_array($roles))$roles=[$roles];return in_array($_SESSION['role']??'',$roles);}
    
    public function getLinkedStudents($parentId){
        $stmt=$this->db->prepare("SELECT s.id,s.full_name,s.class_id,c.name as class_name FROM students s JOIN parent_student ps ON s.id=ps.student_id JOIN classes c ON s.class_id=c.id WHERE ps.parent_id=? ORDER BY s.position");
        $stmt->execute([$parentId]);return $stmt->fetchAll();
    }
}