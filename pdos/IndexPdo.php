<?php



// 회원가입
function createUser($userEmail, $password, $nickName, $method, $phoneNum)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO user (
                userEmail,
                password,
                method,
                nickName,
                phoneNum
                ) 
                VALUES (?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userEmail,$password,$nickName,$method,$phoneNum]);

    $st = null;
    $pdo = null;
}

// 회원가입 - 유저 레벨 테이블
function userLevel($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into userLevel (userIdx,levelIdx) value (?,'1');
";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);

    $st = null;
    $pdo = null;
}
// 회원탈퇴
function deleteUser($userEmail)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE user SET isDeleted = 'Y' WHERE (userEmail = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userEmail]);

    $st = null;
    $pdo = null;
}
function getUser($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select userIdx,
                nickName,
                isMaster,
                couponCnt,
                reviewCnt,
                leftPoint,
                levelName
                from user
                left join(
                select 
                userIdx,
                levelIdx
                from userLevel
                )user_level using(userIdx)
                left join(
                select
                levelIdx,
                levelName
                from yogiyoLevel
                )yogiyo_level using(levelIdx)
                left join(
                select
                userIdx,
                count(couponIdx) as couponCnt
                from userCoupon where isDeleted = 'N'
                group by userIdx
                ) couponCnt using (userIdx) 
                left join(
                select
                userIdx,
                count(reviewIdx) as reviewCnt
                from review where isDeleted = 'N'
                group by userIdx
                ) reviewCnt using (userIdx) 
                left join(
                select 
                userIdx,
                    sum(
                    if (pointHistory.plusMinus = '-',
                    howMuch * (-1),
                    howMuch)
                    ) as leftPoint
                    from pointHistory
                    group by userIdx
                )as leftPoint using (userIdx)
                where userIdx =?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getUserDetail($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select userIdx,
                userEmail,
                LPAD('',Length(password),'*')as password,
                nickName,
                phoneNum
                from user
                where userIdx =?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function patchNickName($nickName,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE user SET nickName = ? WHERE userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$nickName,$userIdx]);

    $st = null;
    $pdo = null;
}
function patchPhoneNum($phoneNum,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE user SET phoneNum = ? WHERE userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$phoneNum,$userIdx]);

    $st = null;
    $pdo = null;
}
//////////////////////////////////////////////////////////////////////////////////////
function isValidUser($id,$pw){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM user WHERE userEmail= ? AND password = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$id,$pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isVaildCoupon($couponId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM totalCoupon WHERE couponId= ? and isDeleted='N' and isLevelCoupon='N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$couponId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function postCoupon($couponId,$userIdx){
    $pdo = pdoSqlConnect();
    $query = "insert into userCoupon (couponIdx, userIdx) VALUES ((select couponIdx from totalCoupon where couponId=?),?);";

    $st = $pdo->prepare($query);
    $st->execute([$couponId,$userIdx]);
    $res=null;

    $st = null;
    $pdo = null;

    return $res;
}
function isMyCoupon($couponId,$userIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM userCoupon left outer join totalCoupon using(couponIdx) WHERE couponId= ? and userIdx= ? ) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$couponId,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isRestUser($id,$pw){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM user WHERE userEmail= ? AND password = ? AND isDeleted='Y') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$id,$pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
<<<<<<< HEAD
<<<<<<< HEAD
=======

// 회원가입
function createUser($userEmail, $password, $nickName, $method, $phoneNum)
{
=======
function getAdvertise(){
>>>>>>> 5e5d52175e56436b9844242ba6186ada7f331795
    $pdo = pdoSqlConnect();
    $query = "select adIdx, adImg from advertisement;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function isMaster($userEmail){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT userEmail FROM user WHERE userEmail= ? and isMaster ='Y') AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userEmail]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

<<<<<<< HEAD
>>>>>>> 192fe8be3d89930e0347f92a86187b1a0f7b82ac
=======

function postLevelCoupon(){
    $pdo = pdoSqlConnect();
    $query = "";

    $st = $pdo->prepare($query);
    $st->execute([$couponId,$userIdx]);
    $res=null;

    $st = null;
    $pdo = null;

    return $res;
}
>>>>>>> 5e5d52175e56436b9844242ba6186ada7f331795
// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
