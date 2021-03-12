<?php

//DB 정보
function pdoSqlConnect()
{
    try {
        $DB_HOST = "13.209.19.46";
        $DB_NAME = "yogiyo";
        $DB_USER = "minju";
        $DB_PW = "239600";
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}