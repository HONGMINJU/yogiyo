<?php
function createMasterComment($reviewIdx,$contents){
    $pdo = pdoSqlConnect();
    $query = "insert into masterComment(reviewIdx, comment) values (?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx,$contents]);
    $res = $reviewIdx.'에 사장님댓글을 달았습니다.';

    $st = null;
    $pdo = null;

    return $res;
}
function isAlreadyReviwHeart($reviewIdx,$userIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select * from reviewLike where reviewIdx=? and  userIdx=?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$reviewIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isReviewHeart_L($reviewIdx,$userIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select * from reviewLike where reviewIdx=? and  userIdx=? and status='L')AS exist ";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isVaildReview($reviewIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select * from review where reviewIdx=?)AS exist";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isMyReview($userIdx,$reviewIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select * from review where userIdx=? and  reviewIdx=?)AS exist";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$reviewIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isMyStoreReview($masterIdx,$reviewIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select * from review left outer join store using (storeIdx) where reviewIdx=? and  masterIdx=?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$reviewIdx,$masterIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function deleteMyReview($userIdx,$reviewIdx){
    $pdo = pdoSqlConnect();
    $query = "delete from review where userIdx=? and reviewIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$reviewIdx]);

    $query = "delete from masterComment where reviewIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx]);

    $query = "delete from reviewPhoto where reviewIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx]);

    $res = null;

    $st=null;
    $pdo = null;

}
function isAlreadyMasterReview($reviewIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select * from masterComment where reviewIdx=?)AS exist";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function createReviewHeart($reviewIdx,$userIdx){
    $pdo = pdoSqlConnect();
    $query = "insert into reviewLike(reviewIdx, userIdx, status) VALUES (?,?,'L');";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res=$reviewIdx;
    $st=null;
    $pdo = null;

    return $res;
}
function patchReviewHeart_N($reviewIdx,$userIdx){
    $pdo = pdoSqlConnect();
    $query = "update reviewLike set status ='N' where reviewIdx = ? and userIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res=$reviewIdx;

    $st=null;
    $pdo = null;

    return $res;
}
function patchReviewHeart_L($reviewIdx,$userIdx){
    $pdo = pdoSqlConnect();
    $query = "update reviewLike set status ='L' where reviewIdx = ? and userIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res=$reviewIdx;

    $st=null;
    $pdo = null;

    return $res;
}
function patchMasterComment($reviewIdx,$contents){
    $pdo = pdoSqlConnect();
    $query = "update masterComment set comment =? where reviewIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$contents,$reviewIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res=$reviewIdx;

    $st=null;
    $pdo = null;

    return $res;
}
function getStoreReview($storeIdx,$sorting,$isDesc){
    $pdo = pdoSqlConnect();
    $query = "select storeIdx,
       (select count( distinct reviewIdx)from review left outer join reviewPhoto using(reviewIdx)where storeIdx=? and photoUrl is not null)as photoReview,
       (select count( distinct reviewIdx)from review left outer join  masterComment using(reviewIdx) where storeIdx=? and masterComment.comment is not null)as masterComments,
       count(distinct reviewIdx)as review,
       FORMAT(avg(totalScore),1)as avgTotalStar,
       FORMAT(avg(tasteScore),1) as avgTasteStar,
       FORMAT(avg(amountScore),1)as avgAmountStar,
       FORMAT(avg(deliveryScore),1)as avgDeliveryStar
from review  group by storeIdx having storeIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$storeIdx,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $query ="select reviewIdx,photoUrl,concat(left(userEmail,2),'**님') as userID,totalScore,tasteScore,amountScore,deliveryScore,
       (case when TIMESTAMPDIFF(MINUTE, review.createdAt,now())<60
                then concat(TIMESTAMPDIFF(MINUTE, review.createdAt,now()),'분 전')
           when TIMESTAMPDIFF(HOUR, review.createdAt,now())<24
               then concat(TIMESTAMPDIFF(HOUR, review.createdAt,now()),'시간 전')
           when TIMESTAMPDIFF(DAY, review.createdAt,now())=1
               then '어제'
           when TIMESTAMPDIFF(DAY, review.createdAt,now())<7
               then concat(TIMESTAMPDIFF(DAY, review.createdAt,now()),'일 전')
           when TIMESTAMPDIFF(DAY, review.createdAt,now())=7
               then concat(TIMESTAMPDIFF(DAY, review.createdAt,now()),'1주 전')
           else DATE_FORMAT(review.createdAt, '%Y년 %m월 %d일')
           end )as reviewTime,comment,menu
from review left outer join reviewPhoto using(reviewIdx) left outer join user using(userIdx)left outer join(select orderIdx,group_concat(whatmenu separator ', ')as menu from orderList left outer join
                     (select orderIdx, (case when optionResult is not null
    then concat(menuName,'/',count,'(',optionResult ,')')
    else concat(menuName,'/',count)
    end )as whatmenu
         from (orderMenuList left outer join (select menuIdx,menuName from menu) as a using(menuIdx))
         left outer join (select menuOptionIdx,group_concat(optionName) as optionResult  from menuOptionTable
         left outer join (select optionSelectIdx,concat(optionName,'(',optionSelectName,')')as optionName from optiontable
         left outer join optionSelect using(optionIdx))as optionNameTable using(optionSelectIdx)group by menuOptionIdx)
                    as menuOption using (menuOptionIdx)
        group by menuOptionIdx,orderIdx,menuIdx,menuName,count) as menutable
                        using(orderIdx) group by (orderIdx) )as whatMenuOption using(orderIdx)
 where storeIdx=? and photoUrl is not null
  order by review.createdAt desc limit 10;";
    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $photo = $st->fetchAll();
    $res[0]["photoReviews"] =$photo;


    if($isDesc=="D"){
        $query = "select reviewIdx,review.userIdx,orderIdx,levelPhoto,concat(left(userEmail,2),'**님') as userID,
       (case when TIMESTAMPDIFF(MINUTE, review.createdAt,now())<60
                then concat(TIMESTAMPDIFF(MINUTE, review.createdAt,now()),'분 전')
           when TIMESTAMPDIFF(HOUR, review.createdAt,now())<24
               then concat(TIMESTAMPDIFF(HOUR, review.createdAt,now()),'시간 전')
           when TIMESTAMPDIFF(DAY, review.createdAt,now())=1
               then '어제'
           when TIMESTAMPDIFF(DAY, review.createdAt,now())<7
               then concat(TIMESTAMPDIFF(DAY, review.createdAt,now()),'일 전')
           when TIMESTAMPDIFF(DAY, review.createdAt,now())=7
               then concat(TIMESTAMPDIFF(DAY, review.createdAt,now()),'1주 전')
           else DATE_FORMAT(review.createdAt, '%Y년 %m월 %d일')
           end )as reviewTime,
       totalScore,tasteScore,amountScore,deliveryScore,
       menu,
       review.comment as reviewComment,
        count(if(reviewLike.status='L',reviewLike.reviewIdx,null)) as reviewHeart,
       masterComment.comment as masterComment,
       (case when TIMESTAMPDIFF(MINUTE, masterComment.createdAt,now())<60
           then concat(TIMESTAMPDIFF(MINUTE, masterComment.createdAt,now()),'분 전')
           when TIMESTAMPDIFF(HOUR, masterComment.createdAt,now())<24
               then concat(TIMESTAMPDIFF(HOUR, masterComment.createdAt,now()),'시간 전')
           when TIMESTAMPDIFF(DAY, masterComment.createdAt,now())=1
               then '어제'
           when TIMESTAMPDIFF(DAY, masterComment.createdAt,now())<7
               then concat(TIMESTAMPDIFF(DAY, masterComment.createdAt,now()),'일 전')
           when TIMESTAMPDIFF(DAY, masterComment.createdAt,now())=7
               then concat(TIMESTAMPDIFF(DAY, masterComment.createdAt,now()),'1주 전')
           else DATE_FORMAT(masterComment.createdAt, '%Y년 %m월 %d일')
           end )as masterCommentTime
        from (review left outer join(select orderIdx,group_concat(whatmenu separator ', ')as menu from orderList left outer join
                     (select orderIdx, (case when optionResult is not null
    then concat(menuName,'/',count,'(',optionResult ,')')
    else concat(menuName,'/',count)
    end )as whatmenu
         from (orderMenuList left outer join (select menuIdx,menuName from menu) as a using(menuIdx))
         left outer join (select menuOptionIdx,group_concat(optionName) as optionResult  from menuOptionTable
         left outer join (select optionSelectIdx,concat(optionName,'(',optionSelectName,')')as optionName from optiontable
         left outer join optionSelect using(optionIdx))as optionNameTable using(optionSelectIdx)group by menuOptionIdx)
                    as menuOption using (menuOptionIdx)
        group by menuOptionIdx,orderIdx,menuIdx,menuName,count) as menutable
                        using(orderIdx) group by (orderIdx) )as whatMenuOption using(orderIdx)
             left outer join (select userIdx,photoUrl as levelPhoto,userEmail
                                from user left outer join userLevel using (userIdx)
                                left outer join yogiyoLevel using (levelIdx)) as levelInfo using (userIdx))
            left outer join masterComment using(reviewIdx)
            left outer join reviewLike using(reviewIdx)
group by reviewIdx,userIdx,userEmail,levelPhoto,masterComment.comment,masterComment.createdAt,storeIdx,review.createdAt
having storeIdx=?
order by ".$sorting." desc;";
        $st = $pdo->prepare($query);
        $st->execute([$storeIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $review = $st->fetchAll();

        $cnt=count($review);
        for($i=0;$i<$cnt;$i=$i+1){
            $reviewIdx= $review[$i]["reviewIdx"];
            $pdo = pdoSqlConnect();
            $query = "select photoUrl from reviewPhoto left outer join review  using (reviewIdx) where reviewIdx=?;";
            $st = $pdo->prepare($query);
            $st->execute([$reviewIdx]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $testPhotos=$st->fetchAll();
            $photoArray=array();
            foreach ($testPhotos as $testPhoto){
                array_push($photoArray,$testPhoto['photoUrl']);
            }
            $review[$i]["photo"]=$photoArray;
        }

        $res[0]["reviews"] =$review;
    }
    else{
        $query = "select reviewIdx,review.userIdx,orderIdx,levelPhoto,concat(left(userEmail,2),'**님') as userID,
       (case when TIMESTAMPDIFF(MINUTE, review.createdAt,now())<60
                then concat(TIMESTAMPDIFF(MINUTE, review.createdAt,now()),'분 전')
           when TIMESTAMPDIFF(HOUR, review.createdAt,now())<24
               then concat(TIMESTAMPDIFF(HOUR, review.createdAt,now()),'시간 전')
           when TIMESTAMPDIFF(DAY, review.createdAt,now())=1
               then '어제'
           when TIMESTAMPDIFF(DAY, review.createdAt,now())<7
               then concat(TIMESTAMPDIFF(DAY, review.createdAt,now()),'일 전')
           when TIMESTAMPDIFF(DAY, review.createdAt,now())=7
               then concat(TIMESTAMPDIFF(DAY, review.createdAt,now()),'1주 전')
           else DATE_FORMAT(review.createdAt, '%Y년 %m월 %d일')
           end )as reviewTime,
       totalScore,tasteScore,amountScore,deliveryScore,
       menu,
       review.comment as reviewComment,
        count(if(reviewLike.status='L',reviewLike.reviewIdx,null)) as reviewHeart,
       masterComment.comment as masterComment,
       (case when TIMESTAMPDIFF(MINUTE, masterComment.createdAt,now())<60
           then concat(TIMESTAMPDIFF(MINUTE, masterComment.createdAt,now()),'분 전')
           when TIMESTAMPDIFF(HOUR, masterComment.createdAt,now())<24
               then concat(TIMESTAMPDIFF(HOUR, masterComment.createdAt,now()),'시간 전')
           when TIMESTAMPDIFF(DAY, masterComment.createdAt,now())=1
               then '어제'
           when TIMESTAMPDIFF(DAY, masterComment.createdAt,now())<7
               then concat(TIMESTAMPDIFF(DAY, masterComment.createdAt,now()),'일 전')
           when TIMESTAMPDIFF(DAY, masterComment.createdAt,now())=7
               then concat(TIMESTAMPDIFF(DAY, masterComment.createdAt,now()),'1주 전')
           else DATE_FORMAT(masterComment.createdAt, '%Y년 %m월 %d일')
           end )as masterCommentTime
        from (review left outer join(select orderIdx,group_concat(whatmenu separator ', ')as menu from orderList left outer join
                     (select orderIdx, (case when optionResult is not null
    then concat(menuName,'/',count,'(',optionResult ,')')
    else concat(menuName,'/',count)
    end )as whatmenu
         from (orderMenuList left outer join (select menuIdx,menuName from menu) as a using(menuIdx))
         left outer join (select menuOptionIdx,group_concat(optionName) as optionResult  from menuOptionTable
         left outer join (select optionSelectIdx,concat(optionName,'(',optionSelectName,')')as optionName from optiontable
         left outer join optionSelect using(optionIdx))as optionNameTable using(optionSelectIdx)group by menuOptionIdx)
                    as menuOption using (menuOptionIdx)
        group by menuOptionIdx,orderIdx,menuIdx,menuName,count) as menutable
                        using(orderIdx) group by (orderIdx) )as whatMenuOption using(orderIdx)
             left outer join (select userIdx,photoUrl as levelPhoto,userEmail
                                from user left outer join userLevel using (userIdx)
                                left outer join yogiyoLevel using (levelIdx)) as levelInfo using (userIdx))
            left outer join masterComment using(reviewIdx)
            left outer join reviewLike using(reviewIdx)
group by reviewIdx,userIdx,userEmail,levelPhoto,masterComment.comment,masterComment.createdAt,storeIdx,review.createdAt
having storeIdx=? order by ".$sorting.";";
        $st = $pdo->prepare($query);
        $st->execute([$storeIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $review = $st->fetchAll();

        $cnt=count($review);
        for($i=0;$i<$cnt;$i=$i+1){
            $reviewIdx= $review[$i]["reviewIdx"];
            $pdo = pdoSqlConnect();
            $query = "select photoUrl from reviewPhoto left outer join review  using (reviewIdx) where reviewIdx=?;";
            $st = $pdo->prepare($query);
            $st->execute([$reviewIdx]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $testPhotos=$st->fetchAll();
            $photoArray=array();
            foreach ($testPhotos as $testPhoto){
                array_push($photoArray,$testPhoto['photoUrl']);
            }
            $review[$i]["photo"]=$photoArray;
        }

        $res[0]["reviews"] =$review;
    }


    $st=null;
    $pdo = null;

    return $res;
}
function getMyReviewNotYet($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select storeIdx,storePhoto,storeName,
      DATE_FORMAT(orderList.createdAt, '%Y.%m.%d')as orderTime,
       menu,concat(TIMESTAMPDIFF(DAY, orderList.createdAt,now()),'일 남음')as restTime
from orderList left outer join (select orderIdx,group_concat(whatmenu separator ', ')as menu from orderList left outer join
                     (select orderIdx, (case when optionResult is not null
    then concat(menuName,'/',count,'(',optionResult ,')')
    else concat(menuName,'/',count)
    end )as whatmenu
         from (orderMenuList left outer join (select menuIdx,menuName from menu) as a using(menuIdx))
         left outer join (select menuOptionIdx,group_concat(optionName) as optionResult  from menuOptionTable
         left outer join (select optionSelectIdx,concat(optionName,'(',optionSelectName,')')as optionName from optiontable
         left outer join optionSelect using(optionIdx))as optionNameTable using(optionSelectIdx)group by menuOptionIdx)
                    as menuOption using (menuOptionIdx)
        group by menuOptionIdx,orderIdx,menuIdx,menuName,count) as menutable
                        using(orderIdx) group by (orderIdx) )as t using(orderIdx)
            left outer JOIN store using (storeIdx)
where userIdx=? and (orderIdx not in (select orderIdx from review) and TIMESTAMPDIFF(DAY, orderList.createdAt,now())<=7);";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res;
}
function getMyReviewAlready($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select reviewIdx,storeIdx,storePhoto,storeName,
      DATE_FORMAT(review.createdAt, '%Y.%m.%d')as reviewTime,
       totalScore,tasteScore,amountScore,deliveryScore,
       menu,
       review.comment as reviewComment
        from (review left outer join(select orderIdx,group_concat(whatmenu separator ', ')as menu from orderList left outer join
                     (select orderIdx, (case when optionResult is not null
    then concat(menuName,'/',count,'(',optionResult ,')')
    else concat(menuName,'/',count)
    end )as whatmenu
         from (orderMenuList left outer join (select menuIdx,menuName from menu) as a using(menuIdx))
         left outer join (select menuOptionIdx,group_concat(optionName) as optionResult  from menuOptionTable
         left outer join (select optionSelectIdx,concat(optionName,'(',optionSelectName,')')as optionName from optiontable
         left outer join optionSelect using(optionIdx))as optionNameTable using(optionSelectIdx)group by menuOptionIdx)
                    as menuOption using (menuOptionIdx)
        group by menuOptionIdx,orderIdx,menuIdx,menuName,count) as menutable
                        using(orderIdx) group by (orderIdx) )as whatMenuOption using(orderIdx))
            left outer JOIN store using (storeIdx)
            left outer join reviewLike using(reviewIdx)
group by reviewIdx,storeIdx,review.createdAt,review.userIdx having review.userIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    $cnt=count($res);
    for($i=0;$i<$cnt;$i=$i+1){
        $reviewIdx= $res[$i]["reviewIdx"];
        $pdo = pdoSqlConnect();
        $query = "select photoUrl from reviewPhoto left outer join review  using (reviewIdx) where reviewIdx=?;";
        $st = $pdo->prepare($query);
        $st->execute([$reviewIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $testPhotos=$st->fetchAll();
        $photoArray=array();
        foreach ($testPhotos as $testPhoto){
            array_push($photoArray,$testPhoto['photoUrl']);
        }
        $res[$i]["photo"]=$photoArray;
    }

    return $res;
}
function deleteMasterComment($reviewIdx){
    $pdo = pdoSqlConnect();
    $query = "delete from masterComment where reviewIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx]);
}













