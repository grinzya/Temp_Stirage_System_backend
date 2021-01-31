<?php
$username = null;
$password = null;
//адрес хоста
$dbhost = 'hostname_or_ip';
//Имя пользовтаеля
$dbuser = 'dbuser';
//Пароль
$dbpass = 'dbpass';
//имя базы данных
$dbname = 'dbname';
// Ответ на OPTIONS запрос
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Origin, Authorization, Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, X-PINGARUNER');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT');
    header('Access-Control-Max-Age: 86400');
    header('HTTP/1.1 200 OK');
} else {
    // декодируем заголовок с basic аутентификацией
    if (isset($_SERVER['PHP_AUTH_USER'])) {
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        // most other servers
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {

        if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']), 'basic') === 0) {
            list($username, $password) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        }

    }
    //Ищем логин и пароль в базе
    $query = "SELECT id FROM user_table WHERE login = '$username' AND password = '$password' ";
        // Create connection
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    $result = mysqli_query($conn, $query);
    mysqli_close();

    if ($result == false) {
        echo 'Произошла ошибка при выполнении запроса';
    } else {
        if (mysqli_num_rows($result) != 1) {

            header('WWW-Authenticate: Basic realm=""');
            header('HTTP/1.1 401 Unauthorized');
            echo 'Вы не прошли аутентификацию';

            die();
        // Обработка GET запроса от системы визуализации
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                //выделяем тело запроса из ссылки
                $S = $_SERVER['REQUEST_URI'];
                //удаляем лишние символы
                $S = substr($S, 9);
                //выделяем значения
                parse_str($S, $output);
                $latitudeFrom = $output['latitudeFrom'];
                $latitudeTo = $output['latitudeTo'];
                $longitudeFrom = $output['longitudeFrom'];
                $longitudeTo = $output['longitudeTo'];
                $dateTimeFrom = $output['dateTimeFrom'];
                $dateTimeTo = $output['dateTimeTo'];
                //производим запрос из базы MariaDB
                $con_sel = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
                $query_sel = "SELECT * FROM `value_table` WHERE ((`time_value` > '$dateTimeFrom') && (`time_value` <= '$dateTimeTo') && (`latitude` > '$latitudeFrom') && (`latitude` <= '$latitudeTo') && (`longitude` > '$longitudeFrom') && (`longitude` <= '$longitudeTo'))";
                $req_result = mysqli_query($con_sel, $query_sel);
                mysqli_close();
                if ($req_result == false) {
                    echo 'Произошла ошибка при выполнении запроса';
                } else {
                    if (mysqli_num_rows($req_result) >= 1) {

                        header('Access-Control-Allow-Credentials: true');
                        header('Access-Control-Allow-Headers: Origin, Authorization, Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, X-PINGARUNER');
                        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT');
                        header('Access-Control-Max-Age: 86400');
                        header('HTTP/1.1 200 OK');

                        $myArray = array();
                        //преобразуем результат запроса в массив
                        while ($row = $req_result->fetch_array(MYSQLI_ASSOC)) {
                            $row['values'] = [
                                'id' => $row['id'],
                                'temperature' => $row['temperature'],
                                'pressure' => $row['pressure'],
                                'humidity' => $row['humidity'],
                            ];
                            $row['dateTime'] = $row['time_value'];
                            $row['deviceName'] = $row['name'];

                            unset($row['time_value']);
                            unset($row['name']);
                            unset($row['temperature']);
                            unset($row['pressure']);
                            unset($row['humidity']);

                            $myArray[] = $row;

                        }
                        //отдаём json ответ системе визуализации
                        echo json_encode($myArray);

                    }
                }

            } else {
                //декодируем тело post запроса
                $data = json_decode(file_get_contents("php://input"));

                $conn2 = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
                //выделяем id пользователя
                $row = mysqli_fetch_array($result);
                $r = $row['id'];
                // выделяем переменные из массива
                $temp = $data->values->temperature;
                $hum = $data->values->humidity;
                $p = $data->values->pressure;
                //пишем значения в базу
                $insert_query = "INSERT INTO `value_table` (`id_login`, `name`, `temperature`, `time_value`, `latitude`, `longitude`, `humidity`, `pressure`) VALUES ($r, '$data->deviceName', '$temp', current_timestamp(), '$data->latitude', '$data->longitude', $hum, $p)";
                $req_result = mysqli_query($conn2, $insert_query);
                if ($req_result == false) {
                    //сообщаем об ошибке при попытке записи в базу
                    header('HTTP/1.1  418');
                } else {
                    //заголовок при успешной обработке
                    header('HTTP/1.1 200 OK');
                }

            }
        }
    }
}
