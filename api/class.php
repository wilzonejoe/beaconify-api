<?php
    require('tokenValidation.php');
    $headers = apache_request_headers();
    if(isset($headers['Authorization'])){
        $authorizationHeader = $headers['Authorization'];
    }else{
        $authorizationHeader = '';
    }

    $reponse = array();

    if(($userOwnId = validateToken($authorizationHeader)) > 0){
        if(strcasecmp($_SERVER['REQUEST_METHOD'], 'GET') == 0){
            $_SERVER['REQUEST_URI_PATH'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathSegments = explode('/', $_SERVER['REQUEST_URI_PATH']);
            $classId= $pathSegments[count($pathSegments)-1];

            require('../config.php');

            $conn = mysqli_connect($sql_host, $sql_user, $sql_pass, $sql_db);

            if(strcasecmp($classId, 'class.php') != 0){
                $sql_get_class = "SELECT * FROM class WHERE id='$classId'";
                $result = mysqli_query($conn,$sql_get_class);
                if (mysqli_num_rows($result)==0) {
                    http_response_code(404);
                    $response["statusCode"] = 404;
                    $response["message"] = "Class not found"; 
                } else {
                    $row= mysqli_fetch_assoc($result);
                    $responseClass = array();
                    $responseClass["roomNo"]  = $row["roomNo"];
                    $responseClass["streamCode"] = $row["streamCode"];
                    $responseClass["start"] = $row["start"];
                    $responseClass["end"] = $row["end"];
                    $responseClass["paperCode"] = $row["paperCode"];
                    $responseClass["paperName"] = $row["paperName"];

                    http_response_code(200);
                    $response["statusCode"] = 200;
                    $response["user"] = $responseClass;
                }
            }else{
                $sql_get_class = "SELECT c.RoomNo 'roomNo', s.code 'streamCode', s.day 'day', s.time_start 'start', s.time_end 'end', p.Code 'paperCode', p.Name 'paperName' FROM paper p, class c, stream s, users_stream us WHERE c.ID = s.classId AND us.stream_id = s.ID AND us.user_id = '$userOwnId'";
                $result = mysqli_query($conn,$sql_get_class);
                $reponseCollectionOfClasses = array();

                while ($row= mysqli_fetch_assoc($result)) {
                    $responseClass = array();
                    $responseClass["roomNo"]  = $row["roomNo"];
                    $responseClass["streamCode"] = $row["streamCode"];
                    $responseClass["start"] = $row["start"];
                    $responseClass["end"] = $row["end"];
                    $responseClass["paperCode"] = $row["paperCode"];
                    $responseClass["paperName"] = $row["paperName"];
                    array_push($reponseCollectionOfClasses, $responseClass);
                }
                http_response_code(200);
                $response["statusCode"] = 200;
                $response["class"] = $reponseCollectionOfClasses;
            }
        }else{
            http_response_code(405);
            $response["statusCode"] = 405;
            $response["message"] = "Request not supported";
        }
    }else{
        http_response_code(401);
        $response["statusCode"] = 401;
        $response["message"] = "Unauthorized";
    }
    echo json_encode($response);
?>