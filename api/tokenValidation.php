<?php
    
    function validateToken($authorizationHeader) {
        $token = null;
        
        if(isset($authorizationHeader)){
            require('../config.php');
            $conn = mysqli_connect($sql_host, $sql_user, $sql_pass, $sql_db);

            if (!$conn) {
                return 0; 
            } else {
                $sql_check_token_issued = "SELECT * FROM accesstoken where accessToken = '$authorizationHeader'";
                $result = mysqli_query($conn,$sql_check_token_issued);
                if (mysqli_num_rows($result)==0) {
                    return 0;
                } else{
                    $row = mysqli_fetch_assoc($result);
                    return $row["userId"];
                }
            }
        } 
    }

    function generateToken($userId){
        $token= bin2hex(random_bytes(64));
        require('../config.php');
        $conn = mysqli_connect($sql_host, $sql_user, $sql_pass, $sql_db);

        if (!$conn) {
            return null; 
        }else{
            $sql_check_token_issued = "SELECT * FROM accesstoken where userId = '$userId'";
            $result = mysqli_query($conn,$sql_check_token_issued);
            if (mysqli_num_rows($result) == 0) {
                $tokenSql = "insert into accesstoken (accessToken, userId) values ('$token','$userId')";
                $resultToken = mysqli_query($conn, $tokenSql);
                if (!$resultToken) {
                    return null; 
                }else{
                    return $token;
                }
            }else{
                $tokenSql = "update accesstoken set accessToken = '$token' where userId = '$userId'";
                $resultToken = mysqli_query($conn, $tokenSql);
                if (!$resultToken) {
                    return null; 
                }else{
                    return $token;
                }
            }
        }                  
    }
?>