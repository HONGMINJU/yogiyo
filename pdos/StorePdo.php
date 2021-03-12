<?php
function getStores($categoryIdx,$latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select 'plusStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from store left outer join store_category using(storeidx) left join review  using(storeIdx)
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx)
    left outer join redWeek using (storeIdx)
    left outer join  menu using (storeIdx)
left outer join storeAddress using (storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,
         discount.isDeleted,redWeek.isDeleted,isPlus,latitude,longitude
having(categoryIdx=? and isPlus='Y'and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) )
union all
select 'redWeekStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg((totalScore)),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from store left outer join store_category using(storeidx) left join review  using(storeIdx)
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx)
    left outer join redWeek using (storeIdx)
    left outer join  menu using (storeIdx)
left outer join storeAddress using (storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,
         redWeek.isDeleted,isPlus,isDiscount,latitude,longitude
having(categoryIdx=? and isDiscount='R' and mid(redWeek.whenDiscount,weekday(now())+1,1)='Y'
           and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3))
union all
select 'normalStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join storeAddress using (storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,latitude,longitude
having(categoryIdx=? and
       ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3));";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude,$categoryIdx,$latitude,$longitude,$latitude,$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select 'plusStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from store left outer join store_category using(storeidx) left join review  using(storeIdx)
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx)
    left outer join redWeek using (storeIdx)
    left outer join  menu using (storeIdx)
left outer join storeAddress using (storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,
         discount.isDeleted,redWeek.isDeleted,isPlus,latitude,longitude,canYogiyoPay,canCard,canCash
having(categoryIdx=? and isPlus='Y'and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
union all
select 'redWeekStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from store left outer join store_category using(storeidx) left join review  using(storeIdx)
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx)
    left outer join redWeek using (storeIdx)
    left outer join  menu using (storeIdx)
left outer join storeAddress using (storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,
         redWeek.isDeleted,isPlus,isDiscount,latitude,longitude,canCard,canCash,canYogiyoPay
having(categoryIdx=? and isDiscount='R' and mid(redWeek.whenDiscount,weekday(now())+1,1)='Y'
           and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
union all
select 'normalStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join storeAddress using (storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount
       ,latitude,longitude,canYogiyoPay,canCash,canCard
having(categoryIdx=? and
       ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
    and ".$pay."='Y');";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude,
            $categoryIdx,$latitude,$longitude,$latitude,
            $categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getStores_sortByDelivery($categoryIdx,$latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       deliveryCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude
having(categoryIdx=? and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3))
order by (deliveryCharge);";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else {
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       deliveryCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,
       isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude, canYogiyoPay,canCard,canCash
having(categoryIdx=?  and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
order by (deliveryCharge);";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx, $latitude, $longitude, $latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getStores_sortByStar($categoryIdx,$latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       deliveryCharge,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude
having(categoryIdx=? and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3))
order by star DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude,canCash,canCard,canYogiyoPay
having(categoryIdx=? and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
order by star DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getStores_sortByReview($categoryIdx,$latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       deliveryCharge,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude
having(categoryIdx=? and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3))
order by reviews DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       deliveryCharge,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude,canCash,canCard,canYogiyoPay
having(categoryIdx=? and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
order by reviews DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getStores_sortByMinPrice($categoryIdx,$latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       minimumCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,minimumCharge,latitude,longitude
having(categoryIdx=? and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3))
order by (minimumCharge);";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       minimumCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,minimumCharge
       ,latitude,longitude,canYogiyoPay,canCard,canCash
having(categoryIdx=? and
       ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
    and ".$pay."='Y')
order by (minimumCharge);";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getStores_sortByDistance($categoryIdx,$latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       minimumCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen,
       (6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude)))) as Distance
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,minimumCharge,latitude,longitude
having(categoryIdx=? and (Distance<=3))
order by Distance desc;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude,$categoryIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       minimumCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen,
       (6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude)))) as Distance
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,minimumCharge
       ,latitude,longitude,canCash,canCard,canYogiyoPay
having(categoryIdx=? and (Distance<=3) and ".$pay."='Y')
order by Distance desc;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude,$categoryIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getStores_sortByDiscount($categoryIdx,$latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude
having(categoryIdx=?
           and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
    and isDiscount='D')
order by discount.percent DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude,canCash,canCard,canYogiyoPay
having(categoryIdx=?
           and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
    and isDiscount='D'and ".$pay."='Y')
order by discount.percent DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getStores_sortByMasterComment($categoryIdx,$latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude
having(categoryIdx=? and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3))
order by masterComments DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg((tasteScore+amountScore+deliveryScore)/3),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude,canCash,canCard,canYogiyoPay
having(categoryIdx=? and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
order by masterComments DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getStores_sortByDeliveryTime($categoryIdx,$latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude,minDelivery
having(categoryIdx=? and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3))
order by minDelivery;";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left outer join store_category using(storeidx) left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,categoryIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude,minDelivery,canCard,canCash,canYogiyoPay
having(categoryIdx=? 
           and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
    and ".$pay."='Y'
    )
order by minDelivery;";
        $st = $pdo->prepare($query);
        $st->execute([$categoryIdx,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}

function getALLStores($latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select 'plusStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from store left join review  using(storeIdx)
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx)
    left outer join redWeek using (storeIdx)
    left outer join  menu using (storeIdx)
left outer join storeAddress using (storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,
         discount.isDeleted,redWeek.isDeleted,isPlus,latitude,longitude
having(isPlus='Y'and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) )
union all
select 'redWeekStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from store left join review  using(storeIdx)
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx)
    left outer join redWeek using (storeIdx)
    left outer join  menu using (storeIdx)
left outer join storeAddress using (storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,
         redWeek.isDeleted,isPlus,isDiscount,latitude,longitude
having(isDiscount='R' and mid(redWeek.whenDiscount,weekday(now())+1,1)='Y'
           and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3))
union all
select 'normalStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store  left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join storeAddress using (storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,latitude,longitude
having((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3);";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude,$latitude,$longitude,$latitude,$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select 'plusStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from store left join review  using(storeIdx)
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx)
    left outer join redWeek using (storeIdx)
    left outer join  menu using (storeIdx)
left outer join storeAddress using (storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,
         discount.isDeleted,redWeek.isDeleted,isPlus,latitude,longitude,canYogiyoPay,canCard,canCash
having(isPlus='Y'and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
union all
select 'redWeekStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
       group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from store left join review  using(storeIdx)
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx)
    left outer join redWeek using (storeIdx)
    left outer join  menu using (storeIdx)
left outer join storeAddress using (storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,
         redWeek.isDeleted,isPlus,isDiscount,latitude,longitude,canCard,canCash,canYogiyoPay
having(isDiscount='R' and mid(redWeek.whenDiscount,weekday(now())+1,1)='Y'
           and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
union all
select 'normalStore'as KindOfStore,
       storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join storeAddress using (storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount
       ,latitude,longitude,canYogiyoPay,canCash,canCard
having((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude)))<=3)
    and ".$pay."='Y');";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude,
            $latitude,$longitude,$latitude,
            $latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getALLStores_sortByDelivery($latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       deliveryCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
       group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude
having((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
order by (deliveryCharge);";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else {
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       deliveryCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,
       isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude, canYogiyoPay,canCard,canCash
having(((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
order by (deliveryCharge);";
        $st = $pdo->prepare($query);
        $st->execute([$latitude, $longitude, $latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getALLStores_sortByStar($latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       deliveryCharge,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude
having((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
order by star DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude,canCash,canCard,canYogiyoPay
having(((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
order by star DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getALLStores_sortByReview($latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       deliveryCharge,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
       group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude
having((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
order by reviews DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       deliveryCharge,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude,canCash,canCard,canYogiyoPay
having(((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
order by reviews DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getALLStores_sortByMinPrice($latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       minimumCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,minimumCharge,latitude,longitude
having((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
order by (minimumCharge);";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       minimumCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,minimumCharge
       ,latitude,longitude,canYogiyoPay,canCard,canCash
having(((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
    and ".$pay."='Y')
order by (minimumCharge);";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getALLStores_sortByDistance($latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       minimumCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen,
       (6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude)))) as Distance
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,minimumCharge,latitude,longitude
having(Distance<=3)
order by Distance desc;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       minimumCharge,
       format(ifnull(avg(totalScore),0),1) as star,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
       group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen,
       (6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude)))) as Distance
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,minimumCharge
       ,latitude,longitude,canCash,canCard,canYogiyoPay
having((Distance<=3) and ".$pay."='Y')
order by Distance desc;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getALLStores_sortByDiscount($latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
       group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude
having(((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
    and isDiscount='D')
order by discount.percent DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
       group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude,canCash,canCard,canYogiyoPay
having(((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
    and isDiscount='D'and ".$pay."='Y')
order by discount.percent DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getALLStores_sortByMasterComment($latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
       group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude
having((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
order by masterComments DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
       group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude,canCash,canCard,canYogiyoPay
having(((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3) and ".$pay."='Y')
order by masterComments DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}
function getALLStores_sortByDeliveryTime($latitude,$longitude,$pay){
    if($pay==null){
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
       group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge,latitude,longitude,minDelivery
having(((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3))
order by minDelivery;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "select storeIdx as storeIdx,
       storePhoto as storeImg,
       storeName as storeName,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       isSesco as cesco,
       format(ifnull(avg(totalScore),0),1) as star,
              format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'NEW'
           else '/' end) as isNew,isBest,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case
           when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' and redWeek.isDeleted='N'
           then howMuch
           else 0
           end)as redweek,
        group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))
    left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))
    left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))
    left outer join  storeAddress using(storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,discount.isDeleted,redWeek.isDeleted,isPlus,isDiscount,deliveryCharge
       ,latitude,longitude,minDelivery,canCard,canCash,canYogiyoPay
having(((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)
    and ".$pay."='Y'
    )
order by minDelivery;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;
    }
}


function getHeartStores($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select storeIdx as storeIdx,storePhoto as storeImg,
       storeName as storeName,
       format(count(distinct reviewIdx),0) as reviews,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' then howMuch else 0 end)as redweek,
       group_concat(DISTINCT (CASE WHEN isRepresent='y'and menu.isDeleted='N' then menuName else null end)  separator ', ') as menu,
       (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen
from ((((store left join review  using(storeIdx))left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx))left outer join redWeek using (storeIdx))
    left outer join  menu using (storeIdx))left outer join wishList using (storeIdx)
group by storeIdx,percent,howMuch,redWeek.whenDiscount,wishList.userIdx,status,discount.isDeleted
having(wishList.userIdx=? and status='L');";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getCategories(){
    $pdo = pdoSqlConnect();
    $query = "select storeCategoryIdx,storeCategoryName as categoryName,categoryImg from storeCategory;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function searchStore($keyword,$userIdx,$latitude,$longitude){
    $pdo = pdoSqlConnect();

    $query = "select storeIdx,storeName,storePhoto as storeImg,
       format(count(distinct reviewIdx),0) as reviews,
       if(group_concat(if(menuName like concat('%',?,'%'), menuName, null) separator ', ')is not null ,
           group_concat( distinct if(menuName like concat('%',?,'%'), menuName, null) separator ', '),
           group_concat( distinct if(isRepresent='Y' and menu.isDeleted='N',menuName,null)  separator ', '))as menu,
       format(count(distinct masterCommentIdx),0)as masterComments,
       format(ifnull(avg(totalScore),0),1) as star,
       concat(minDelivery,'~',maxDelivery,'분')as deliveryTime,
       (case when discount.isDeleted='N'
           then discount.percent
           else 0
           end
           ) as discount,
       (case when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' then howMuch else 0 end)as redweek,
              (case when (mid(store.openDay,weekday(now())+1,1)='Y'
                and store.isDeleted='N'
                and(openTime<(HOUR(NOW())+(MINUTE(NOW()))/60)<closeTime
                        or openTime<(HOUR(NOW())+(MINUTE(NOW()))/60+24)<closeTime))
           then 'Y'
           else 'N'
           end
           )as isOpen,isSesco as cesco,isBest,
       (case
           when DATEDIFF(now(),store.createdAt) < 7
           then 'Y'
           else 'N' end) as isNew
from store left outer join menu using(storeIdx) left outer join storeAddress using(storeIdx)
    left join review  using(storeIdx)left outer join masterComment using (reviewIdx)
    left outer join discount using(storeIdx)left outer join redWeek using (storeIdx)
group by storeIdx,storeName,redWeek.whenDiscount,discount.isDeleted,discount.percent,howMuch,latitude,longitude
having((storeName like CONCAT('%', ? ,'%') or menu like CONCAT('%', ? ,'%') and ((6371*acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)
                -radians(?))+sin(radians(?))*sin(radians(latitude))))<=3)));";
    $st = $pdo->prepare($query);
    $st->execute([$keyword,$keyword,$keyword,$keyword,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    if($userIdx!=null){
        insertSearch($userIdx,$keyword);
    }
    $st = null;
    $pdo = null;

    return $res;
}
function insertSearch($userIdx,$keyword){
    $pdo = pdoSqlConnect();
    if(isAlreadyMyKeyword($userIdx,$keyword)){
        $query = "update searchStore set updatedAt=current_time where userIdx=? and contents=?;";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx,$keyword]);
    }
    else{
        $query = "insert into searchStore(userIdx, contents) values (?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx,$keyword]);
    }

}
function getKeyword($userIdx){
    $pdo = pdoSqlConnect();

    $query = "select keywordIdx,contents as keyword from searchStore where userIdx=? order by updatedAt desc ;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function isAlreadyMyKeyword($userIdx,$keyword){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT contents FROM searchStore WHERE userIdx= ? and contents =?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isAlreadyKerwordIdx($KeywordIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT contents FROM searchStore WHERE keywordIdx =?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$KeywordIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isMyKerwordIdx($userIdx,$KeywordIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT contents FROM searchStore WHERE userIdx= ? and keywordIdx =?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$KeywordIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function deleteAllKeyword($userIdx){
    $pdo = pdoSqlConnect();

    $query = "delete from searchStore where userIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $res = null;

    $st = null;
    $pdo = null;

    return $res;
}
function deleteSomeKeyword($userIdx,$KeywordIdx){
    $pdo = pdoSqlConnect();

    $query = "delete from searchStore where userIdx=? and keywordIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$KeywordIdx]);
    $res = null;
    $st = null;
    $pdo = null;

    return $res;
}
function isMyStore($masterIdx,$storeIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select * from store left outer join user on store.masterIdx=user.userIdx where userIdx=? and storeIdx=?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$masterIdx,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function patchStoreMinDelivery($storeIdx,$time){
    $pdo = pdoSqlConnect();
    $query = "update store set minDelivery=? where storeIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$time,$storeIdx]);

    $st=null;
    $pdo = null;

    return null;
}
function patchStoreMaxDelivery($storeIdx,$time){
    $pdo = pdoSqlConnect();
    $query = "update store set maxDelivery=? where storeIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$time,$storeIdx]);

    $st=null;
    $pdo = null;

    return null;
}
function patchStoreMinPrice($storeIdx,$price){
    $pdo = pdoSqlConnect();
    $query = "update store set minimumCharge=? where storeIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$price,$storeIdx]);

    $st=null;
    $pdo = null;

    return null;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function storeIdx_isDiscount($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select isDiscount from store where storeIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return implode('',$res[0]);
}


function isValidStore($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM store WHERE storeIdx= ? AND isDeleted='N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isValidStoreMenu($storeIdx,$menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM menu WHERE storeIdx= ? AND menuIdx = ? and isDeleted='N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isValidStoreCategory($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM storeMenuCategory WHERE menuCategoryIdx= ? AND isDeleted='N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isWishedStore($userIdx,$storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT userIdx FROM wishList WHERE userIdx=? and storeIdx= ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isWishedStatus($userIdx,$storeIdx){
    $pdo = pdoSqlConnect();
    $query = "select status from wishList where userIdx = ? and storeIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return implode('',$res[0]);
}

function offWishedButton($userIdx,$storeIdx){
    $pdo = pdoSqlConnect();
    $query = "update wishList set status = 'N'
               where userIdx = ? and storeIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st=null;
    $pdo = null;

}
function onWishedButton($userIdx,$storeIdx){
    $pdo = pdoSqlConnect();
    $query = "insert into wishList (userIdx,storeIdx,status) value (?, ?,'L');";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st=null;
    $pdo = null;
}
function onWishedButtonBeforeOn($userIdx,$storeIdx){
    $pdo = pdoSqlConnect();
    $query = "update wishList set status = 'L'
                where userIdx = ? and storeIdx =?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st=null;
    $pdo = null;

}


function noDiscount($userIdx,$storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select 
                storeIdx,
                storeName,
                storePhoto,
                totalScore,
                isDiscount,
                concat (minDelivery,'분',' ~ ',maxDelivery,'분') as deliveryTime,
                concat (minimumCharge,' 원') as minimumCharge,
                canYogiyoPay,
                canCash,
                canCard,
                concat (deliveryCharge,' 원') as deliveryCharge,
                masterNotice,
                wishCnt,
                menuCnt,
                reviewCnt,
                case 
                    when (select exists
                            (select 
                                wishList.userIdx
                                from 
                                    wishList
                                    where 
                                    store.storeIdx = wishList.storeIdx
                                    AND 
                                        wishList.userIdx = ? and wishList.status = 'L')
                                            = '1')
                                        THEN 'Y'
                                        ELSE 'N'
                    END as isWished
                from store
                left join(
                select
                storeIdx,
                count(userIdx) as wishCnt
                from wishList where status='L'
                group by storeIdx
                ) wishCnt using (storeIdx)
                
                left join(
                select
                storeIdx,
                count(menuIdx) as menuCnt
                from menu where isDeleted = 'N'
                group by storeIdx
                ) menuCnt using (storeIdx)
                
                left join(
                select
                storeIdx,
                count(reviewIdx) as reviewCnt
                from review where isDeleted = 'N'
                group by storeIdx
                ) reviewCnt using (storeIdx)
                left join(
                select 
                storeIdx,
                Round(avg(totalScore),1) as totalScore
                from review 
                group by storeIdx
                ) score using (storeIdx)
                left join(
                select
                storeIdx,
                concat (latitude,' / ',longitude) as address
                from storeAddress
                group by storeIdx
                ) as address using (storeIdx)
                where isDeleted = 'N' and storeIdx= ?
                ;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function redWeek($userIdx,$storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select 
                storeIdx,
                storeName,
                storePhoto,
                totalScore,
                isDiscount,
                (case 
                    when mid(redWeek.whenDiscount,weekday(now())+1,1)='Y' 
                        and redWeek.isDeleted='N'
                           then concat (howMuch, '원')
                           else 0
                end)as dcWon,
                concat (minDelivery,'분',' ~ ',maxDelivery,'분') as deliveryTime,
                concat (minimumCharge,' 원') as minimumCharge,
                canYogiyoPay,
                canCash,
                canCard,
                concat (deliveryCharge,' 원') as deliveryCharge,
                masterNotice,
                wishCnt,
                menuCnt,
                reviewCnt,
                case 
                    when (select exists
                            (select 
                                wishList.userIdx
                                from 
                                    wishList
                                    where 
                                    store.storeIdx = wishList.storeIdx
                                    AND 
                                        wishList.userIdx = ? and wishList.status = 'L')
                                            = '1')
                                        THEN 'Y'
                                        ELSE 'N'
                    END as isWished
                from store
                left outer join redWeek using (storeIdx)
                left join(
                select
                storeIdx,
                count(userIdx) as wishCnt
                from wishList where status = 'L'
                group by storeIdx
                ) wishCnt using (storeIdx)
                
                left join(
                select
                storeIdx,
                count(menuIdx) as menuCnt
                from menu where isDeleted='N'
                group by storeIdx
                ) menuCnt using (storeIdx)
                
                left join(
                select
                storeIdx,
                count(reviewIdx) as reviewCnt
                from review where isDeleted= 'N'
                group by storeIdx
                ) reviewCnt using (storeIdx)
                
                left join(
                select 
                storeIdx,
                Round(avg(totalScore),1) as totalScore
                from review 
                group by storeIdx
                ) score using (storeIdx)
                left join(
                select
                storeIdx,
                concat (latitude,' / ',longitude) as address
                from storeAddress
                group by storeIdx
                ) as address using (storeIdx)
                where store.isDeleted = 'N' and storeIdx= ?
                ;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function yesDiscount($userIdx,$storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select 
                    storeIdx,
                    storeName,
                    storePhoto,
                    totalScore,
                    isDiscount,
                    concat (dcW, ' % ') as dcPercent,
                    concat (minDelivery,'분',' ~ ',maxDelivery,'분') as deliveryTime,
                    concat (minimumCharge,' 원') as minimumCharge,
                    canYogiyoPay,
                    canCash,
                    canCard,
                    concat (deliveryCharge,' 원') as deliveryCharge,
                    masterNotice,
                    wishCnt,
                    menuCnt,
                    reviewCnt,
                    case 
                        when (select exists
                                (select 
                                    wishList.userIdx
                                    from 
                                        wishList
                                        where 
                                        store.storeIdx = wishList.storeIdx
                                        AND 
                                            wishList.userIdx = ? and wishList.status = 'L')
                                                = '1')
                                            THEN 'Y'
                                            ELSE 'N'
                        END as isWished
                    from store
                    left join(
                    select
                    storeIdx,
                    count(userIdx) as wishCnt
                    from wishList where status = 'L'
                    group by storeIdx
                    ) wishCnt using (storeIdx)
                    
                    left join(
                    select
                    storeIdx,
                    count(menuIdx) as menuCnt
                    from menu where isDeleted = 'N'
                    group by storeIdx
                    ) menuCnt using (storeIdx)
                    
                    left join(
                    select
                    storeIdx,
                    count(reviewIdx) as reviewCnt
                    from review where isDeleted = 'N'
                    group by storeIdx
                    ) reviewCnt using (storeIdx)
                    
                    join(
                    select
                    storeIdx,
                    percent as dcW
                    from discount
                    ) dcW using (storeIdx)
                    left join(
                    select 
                    storeIdx,
                    Round(avg(totalScore),1) as totalScore
                    from review 
                    group by storeIdx
                    ) score using (storeIdx)   
                    left join(
                    select
                    storeIdx,
                    concat (latitude,' / ',longitude) as address
                    from storeAddress
                    group by storeIdx
                    ) as address using (storeIdx)
                    where isDeleted = 'N' and storeIdx= ?
                    ;
";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function storeBestMenu($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select
                menuIdx,
                menuName,
                contents,
                price,
                photoUrl
                from menu
                where isDeleted='N' and storeIdx = ? and isRepresent = 'Y';";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function storeCategory($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select
                menuCategoryIdx,
                categoryName,
                isAlcohol
                from storeMenuCategory
                where isDeleted = 'N' and storeIdx = ?; ";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function storeCategoryMenu($menuCategoryIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select 
                menuIdx,
                menuName,
                contents,
                price,
                photoUrl
                from menu
                where isDeleted = 'N' and menuCategoryIdx = ?; ";

    $st = $pdo->prepare($query);
    $st->execute([$menuCategoryIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function detailInfo($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select 
                        storeIdx,
                        storeName,
                        masterNotice,
                        openDay,
                        concat (openTime,'시',' ~ ',closeTime,'시') as runningTime,
                        callnum,
                        address, 
                        concat (minimumCharge,' 원') as minimumCharge,
                        canYogiyoPay,
                        canCash,
                        canCard,
                        canTakeOut,
                        isSesco,
                        brandName,
                        brandNum,
                        origininfo
                        from store
                        left join(
                        select
                        storeIdx,
                        concat (latitude,' / ',longitude) as address
                        from storeAddress
                        group by storeIdx
                        ) as address using (storeIdx)
                        where isDeleted = 'N' and storeIdx= ?
                        ;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function storeMenuDetail($menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT menuIdx,
                    contents,
                    photoUrl,
                    menuName ,
                    price
                    from menu
                    where menuIdx = ?
                    ;";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $res[0] = $st->fetchAll();

    $query = "select 
                optionSelectIdx,
                optionSelectName,
                plusCharge 
                from menu_option_list 
                left join
                (
                select
                optionSelectIdx,
                optionSelectName,
                plusCharge,
                optionIdx
                from optionSelect
                )option_select using(optionIdx)
                where menuIdx=?;  ";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $res[1] = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isExistCart($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT cartIdx FROM cart WHERE userIdx= ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isExistCartMenuIdx($cartIdx,$cartMenuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT cartMenuIdx FROM cartMenu WHERE cartIdx= ? and cartMenuIdx = ? and count != '0') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$cartIdx,$cartMenuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isExistOrder($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT orderIdx FROM orderList WHERE userIdx= ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isExistOrderDetail($orderIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM orderList WHERE orderIdx= ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isExistMenuCategory($storeIdx,$menuCategoryIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM storeMenuCategory WHERE storeIdx= ? and menuCategoryIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$menuCategoryIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isExistMenu($storeIdx,$menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM menu WHERE storeIdx= ? and menuIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function getCart($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select
                cartIdx,
                storeName,
                menuName,
                price,
                count,
                cartMenuIdx,
                menuOptionIdx,
                group_concat(optionSelectList separator ', ')as menuOption
                from cart
                
                left join(
                select
                storeIdx,
                storeName
                from store
                )st_ore using(storeIdx)
                
                left join(
                select
                cartMenuIdx,
                cartIdx,
                menuIdx,
                count,
                menuOptionIdx
                from cartMenu
                )as cart_menu using(cartIdx)
                
                left join(
                select
                menuIdx,
                menuName,
                price
                from menu
                )as me_nu using(menuIdx)
                
                left join(
                select
                menuOptionIdx,
                optionSelectIdx,
                optionSelectList
                from menuOptionTable
                   left join(
                      select
                      optionIdx,
                      optionSelectIdx,
                      concat(optionName, ' : ',optionSelectName,'( +',plusCharge,' )') as optionSelectList
                      from optionSelect
                           left join(
                            select
                            optionIdx,
                            optionName
                            from optiontable
                            )option_table using(optionIdx)
                   )as option_select using(optionSelectIdx)
                   where isDeleted = 'N'
                )as menuOption_Table using(menuOptionIdx)
                
                group by cartIdx,storeName,menuName,userIdx,isDeleted,price,count,menuOptionIdx,cartMenuIdx
                
                having userIdx=? and isDeleted = 'N' and count != '0'
                ;
";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function postCartIdx($storeIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into cart (storeIdx,userIdx) value (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$userIdx]);

    $st = null;
    $pdo = null;

}
function getCartIdx($storeIdx,$userIdx){

    $pdo = pdoSqlConnect();
    $query = "select cartIdx from cart where storeIdx = ? and userIdx = ? and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["cartIdx"]);
}
function getCartIdx2($userIdx){

    $pdo = pdoSqlConnect();
    $query = "select cartIdx from cart where userIdx= ? and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["cartIdx"]);
}

function deleteCartIdx($storeIdx,$userIdx){

    $pdo = pdoSqlConnect();
    $query = "UPDATE cart SET isDeleted = 'Y' WHERE storeIdx = ? and userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$userIdx]);

    $st = null;
    $pdo = null;
}
function howManyOption($menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select 
                count(optionIdx) as optionCnt
                from menu_option_list
                where menuIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["optionCnt"]);
}
function postCartMenu($cartIdx,$menuIdx,$count)
{
    $pdo = pdoSqlConnect();
    $query = "insert into cartMenu (cartIdx,menuIdx,count) value (?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$cartIdx,$menuIdx,$count]);

    $st = null;
    $pdo = null;

}

// cartMenuIdx 확인
function getCartMenuIdx($cartIdx,$menuIdx,$count){

    $pdo = pdoSqlConnect();
    $query = "select cartMenuIdx from cartMenu where cartIdx=? and menuIdx =? and count =? and menuOptionIdx = 0;";

    $st = $pdo->prepare($query);
    $st->execute([$cartIdx,$menuIdx,$count]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return implode('',$res[0]);
}

function postMenuOptionIdx($cartIdx,$cartMenuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into cartMenuOption (cartIdx,cartMenuIdx) value (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$cartIdx,$cartMenuIdx]);

    $st = null;
    $pdo = null;
}

function getCartMenuOptionIdx($cartIdx,$cartMenuIdx){

    $pdo = pdoSqlConnect();
    $query = "select menuOptionIdx from cartMenuOption where cartIdx = ? and cartMenuIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$cartIdx,$cartMenuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return implode('',$res[0]);
}

function MenuOptionRegister($menuOptionIdx,$optionSelectIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into menuOptionTable (menuOptionIdx,optionSelectIdx) value (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$menuOptionIdx,$optionSelectIdx]);

    $st = null;
    $pdo = null;
}

function updateMenuOptionIdx($menuOptionIdx,$cartIdx,$cartMenuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE cartMenu SET menuOptionIdx = ? WHERE cartIdx = ? and cartMenuIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$menuOptionIdx,$cartIdx,$cartMenuIdx]);

    $st = null;
    $pdo = null;
}

function patchCartCount($countMenu,$cartMenuIdx){

    $pdo = pdoSqlConnect();
    $query = "UPDATE cartMenu SET count = ? WHERE cartMenuIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$countMenu,$cartMenuIdx]);

    $st = null;
    $pdo = null;
}
function deleteCartMenu($cartMenuIdx){

    $pdo = pdoSqlConnect();
    $query = "UPDATE cartMenu SET count = '0' WHERE cartMenuIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$cartMenuIdx]);

    $st = null;
    $pdo = null;
}
function deleteCart($userIdx){

    $pdo = pdoSqlConnect();
    $query = "UPDATE cart SET isDeleted = 'Y' WHERE userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);

    $st = null;
    $pdo = null;
}

function userIdx_cartIdx($userIdx){

    $pdo = pdoSqlConnect();
    $query = "select cartIdx from cart where userIdx = ? and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return implode('',$res[0]);
}
function getMenuCnt($cartIdx){

    $pdo = pdoSqlConnect();
    $query = "select
                count(cartMenuIdx) as cnt
                from cartMenu
                where cartIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$cartIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return implode('',$res[0]);
}
function getOrderCartMenuIdx($cartIdx){

    $pdo = pdoSqlConnect();
    $query = "select cartMenuIdx from cartMenu where cartIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$cartIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $arr = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $arr;
}
// 카트메뉴에서 정보 가져오기
function getCartMenuMenuOptionIdx($cartIdx,$cartMenuIdx){

    $pdo = pdoSqlConnect();
    $query = "select menuOptionIdx from cartMenu where cartIdx = ? and cartMenuIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$cartIdx,$cartMenuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return implode('',$res[0]);
}
function getCartMenuMenuIdx($cartIdx,$cartMenuIdx){

    $pdo = pdoSqlConnect();
    $query = "select menuIdx from cartMenu where cartIdx = ? and cartMenuIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$cartIdx,$cartMenuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return implode('',$res[0]);
}
function getCartMenuCount($cartIdx,$cartMenuIdx){

    $pdo = pdoSqlConnect();
    $query = "select count from cartMenu where cartIdx = ? and cartMenuIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$cartIdx,$cartMenuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return implode('',$res[0]);
}
function getOrderIdx($userIdx){

    $pdo = pdoSqlConnect();
    $query = "select orderIdx 
                from orderList 
                where userIdx = ? and isDeleted = 'N'
                order by createdAt desc limit 1; ";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return implode('',$res[0]);
}
function insertOrderMenuList($cartMenuIdx,$menuOptionIdx,$orderIdx,$menuIdx,$count){

    $pdo = pdoSqlConnect();
    $query = "insert into orderMenuList (cartMenuIdx,menuOptionIdx,orderIdx,menuIdx,count) value (?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$cartMenuIdx,$menuOptionIdx,$orderIdx,$menuIdx,$count]);

    $st = null;
    $pdo = null;

}
function getStoreIdx($userIdx){

    $pdo = pdoSqlConnect();
    $query = "select storeIdx
            from cart
            where userIdx =? and isDeleted = 'N'; ";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return implode('',$res[0]);
}
function postOrder($userIdx,$storeIdx,$toMaster,$payMethod,$safeDelivery,$noSpoon){

    $pdo = pdoSqlConnect();
    $query = "insert into orderList (userIdx,storeIdx,toMaster,payMethod,safeDelivery,noSpoon) value (?,?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$storeIdx,$toMaster,$payMethod,$safeDelivery,$noSpoon]);

    $st = null;
    $pdo = null;

}

function getOrderList($userIdx){

    $pdo = pdoSqlConnect();
    $query = "SELECT orderIdx,storeIdx,
                        storeName,storePhoto,createdAt,safeDelivery,noSpoon FROM orderList
                        left join
                        (
                        select 
                        storeIdx,
                        storeName,
                        storePhoto
                        from store
                        ) st_ore using(storeIdx)
                        where userIdx = ? and isDeleted = 'N'
                        order by createdAt desc
                        ;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $arr = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $arr;
}
function getOrderListDetail($orderIdx){

    $pdo = pdoSqlConnect();
    $query = "select
                orderIdx,
                storeName,
                toMaster,
                payMethod,
                menuName,
                price,
                count,
                menuOptionIdx,
                cartMenuIdx,
                group_concat(optionSelectList separator ', ')as menuOption
                from orderList
                
                left join(
                select
                storeIdx,
                storeName
                from store
                )st_ore using(storeIdx)
                
                left join(
                select
                cartMenuIdx,
                orderIdx,
                menuIdx,
                count,
                menuOptionIdx
                from orderMenuList
                )as order_menu using(orderIdx)
                
                left join(
                select
                menuIdx,
                menuName,
                price
                from menu
                )as me_nu using(menuIdx)
                
                left join(
                select
                menuOptionIdx,
                optionSelectIdx,
                optionSelectList
                from menuOptionTable
                   left join(
                      select
                      optionIdx,
                      optionSelectIdx,
                      concat(optionName, ' : ',optionSelectName,'( +',plusCharge,' )') as optionSelectList
                      from optionSelect
                           left join(
                            select
                            optionIdx,
                            optionName
                            from optiontable
                            )option_table using(optionIdx)
                   )as option_select using(optionSelectIdx)
                   where isDeleted = 'N'
                )as menuOption_Table using(menuOptionIdx)
                group by orderIdx,storeName,menuName,price,isDeleted,count,userIdx,menuOptionIdx,cartMenuIdx,toMaster,payMethod
                having orderIdx=? and isDeleted = 'N' and count != '0';";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $arr = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $arr;
}

function deleteOrderListDetail($orderIdx){

    $pdo = pdoSqlConnect();
    $query = "UPDATE orderList SET isDeleted = 'Y' WHERE orderIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx]);

    $st = null;
    $pdo = null;
}

function postStoreCategory($storeIdx,$categoryName)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO storeMenuCategory (storeIdx, categoryName) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$categoryName]);

    $st = null;
    $pdo = null;

}
function deleteStoreCategory($menuCategoryIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE storeMenuCategory SET isDeleted = 'Y' WHERE menuCategoryIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$menuCategoryIdx]);

    $st = null;
    $pdo = null;

}
function getMasterIdx($storeIdx){

    $pdo = pdoSqlConnect();
    $query = "select masterIdx from store where storeIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["masterIdx"]);
}
function postMenu($menuCategoryIdx,$storeIdx,$contents,$photoUrl,$menuName,$price,$isRepresent)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO menu (menuCategoryIdx, storeIdx,contents,photoUrl, menuName, price,isRepresent) 
                VALUES (?, ?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$menuCategoryIdx,$storeIdx,$contents,$photoUrl,$menuName,$price,$isRepresent]);

    $st = null;
    $pdo = null;

}
function deleteMenu($menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE menu SET isDeleted = 'Y' WHERE menuIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx]);

    $st = null;
    $pdo = null;

}



