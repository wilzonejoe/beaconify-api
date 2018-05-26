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
            $queries = array();
            parse_str($_SERVER['QUERY_STRING'], $queries);

            $currentTime = $queries["time"];
            $currentDay = $queries["day"];

            require('../config.php');

            $conn = mysqli_connect($sql_host, $sql_user, $sql_pass, $sql_db);

            $date = date('Y-m-d');
            $sql_get_class_info = "SELECT c.RoomNo 'roomNo', s.code 'streamCode', p.Code 'paperCode', p.Name 'paperName', cn.Title 'title', cn.Url 'url' FROM content cn, beacon b, class_beacon cb, class c, paper p, stream s, users_stream us WHERE b.id = cb.beaconId AND cb.classId = c.id AND c.id = s.classId AND s.id = us.stream_id AND s.paperId = p.id AND us.user_id = '$userOwnId' AND s.time_start <= '$currentTime' AND s.time_end >= '$currentTime' AND s.day = '$currentDay' AND cn.Date = '$date'";
            $result = mysqli_query($conn,$sql_get_class_info);
            $reponseCollectionOfClasses = array();

            $firstTime = true;
            while ($row= mysqli_fetch_assoc($result)) {
                $responseClass = array();
                if($firstTime) {
                    $response["roomNo"]  = $row["roomNo"];
                    $response["streamCode"] = $row["streamCode"];
                    $response["paperCode"] = $row["paperCode"];
                    $response["paperName"] = $row["paperName"];
                    $firstTime = false;
                }
                $responseClass["title"] = $row["title"];
                $responseClass["url"] = $row["url"];
                array_push($reponseCollectionOfClasses, $responseClass);
            }
            http_response_code(200);
            $response["statusCode"] = 200;
            $response["contents"] = $reponseCollectionOfClasses;
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