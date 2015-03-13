<?php
	set_time_limit(5184000);

	date_default_timezone_set("Europe/Moscow");
	$pdo = new PDO('pgsql:host=10.0.3.194;port=5432;dbname=kiosk_srv', 'kiosk_adm', '@gg3n1n');
	sleep(5);

	$handle = fopen("4.csv", "a") or die("oops");
	fputcsv($handle, array('id'=>'id','name'=>'name','date'=>'date','pings per day'=>'pings per day','pings lost'=>'pings lost','hoursPerDay'=>'hoursPerDay','region'=>'region'),";");
	$today = date('Y-m-d');
	$stmt = $pdo->prepare("SELECT kiosk.id,region.id AS region_id,shop.work_start,shop.work_end, kiosk.name
        FROM region
        LEFT JOIN city ON region.id=city.region_id
        INNER JOIN shop ON city.id=shop.city_id
        INNER JOIN kiosk ON shop.id=kiosk.shop_id

       WHERE kiosk.name NOT LIKE 'X-%'

 			");
	$stmt->execute();
	$shopWorkTimeById  = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$pdo= null;

	$kioskIdRegionId = array();
	$pingsCreated = array();
	$pingsCountDate = array();
	foreach($shopWorkTimeById as $key => $val)
	{

		list($hours, $minutes, $seconds) = explode(":", $val['work_start']);
		$workStartSec = mktime($hours, $minutes, $seconds);

		list($hours, $minutes, $seconds) = explode(":", $val['work_end']);
		$workEndSec = mktime($hours, $minutes, $seconds);

		$hoursPerDay= ($workEndSec - $workStartSec)/3600;
		$hoursPerDay = (int)$hoursPerDay;
		if($hoursPerDay == 0  )
		{
			$hoursPerDay = 24;
		}
		if($hoursPerDay == 23  )
		{
			$hoursPerDay = 24;
		}
//все эк(1317) с регионом и временем работы в часах
		$kioskIdRegionId[] = array(
			'id'=>$val['id'],
			'region_id'=>$val['region_id'],
			'hoursPerDay' => $hoursPerDay,
			'name'=>iconv("utf-8", "windows-1251", $val['name'])
		);


	}

	$msk=array();
	$spb=array();
	$nn=array();
	$kzn=array();
	$ural=array();
	$ug=array();
	$sib=array();
	$dv=array();
	$cr=array();
	foreach($kioskIdRegionId as $reg)
	{

		if($reg['region_id']=='1')
		{
			$msk[] =array(
				'id'=>$reg['id'],
				'region_id'=>$reg['region_id'],
				'hoursPerDay' => $reg['hoursPerDay'],
				'name'=>$reg['name']
			);
		}
		if($reg['region_id']=='2')
		{
			$spb[] =array(
				'id'=>$reg['id'],
				'region_id'=>$reg['region_id'],
				'hoursPerDay' => $reg['hoursPerDay'],
				'name'=>$reg['name']
			);
		}
		if($reg['region_id']=='3')
		{
			$nn[] =array(
				'id'=>$reg['id'],
				'region_id'=>$reg['region_id'],
				'hoursPerDay' => $reg['hoursPerDay'],
				'name'=>$reg['name']
			);
		}
		if($reg['region_id']=='4')
		{
			$kzn[] =array(
				'id'=>$reg['id'],
				'region_id'=>$reg['region_id'],
				'hoursPerDay' => $reg['hoursPerDay'],
				'name'=>$reg['name']
			);
		}
		if($reg['region_id']=='5')
		{
			$ural[] =array(
				'id'=>$reg['id'],
				'region_id'=>$reg['region_id'],
				'hoursPerDay' => $reg['hoursPerDay'],
				'name'=>$reg['name']
			);
		}
		if($reg['region_id']=='6')
		{
			$ug[] =array(
				'id'=>$reg['id'],
				'region_id'=>$reg['region_id'],
				'hoursPerDay' => $reg['hoursPerDay'],
				'name'=>$reg['name']
			);
		}
		if($reg['region_id']=='7')
		{
			$sib[] =array(
				'id'=>$reg['id'],
				'region_id'=>$reg['region_id'],
				'hoursPerDay' => $reg['hoursPerDay'],
				'name'=>$reg['name']
			);
		}
		if($reg['region_id']=='8')
		{
			$dv[] =array(
				'id'=>$reg['id'],
				'region_id'=>$reg['region_id'],
				'hoursPerDay' => $reg['hoursPerDay'],
				'name'=>$reg['name']
			);
		}
		if($reg['region_id']=='11')
		{
			$cr[] =array(
				'id'=>$reg['id'],
				'region_id'=>$reg['region_id'],
				'hoursPerDay' => $reg['hoursPerDay'],
				'name'=>$reg['name']
			);
		}

	}


	/** @var $msk array */
	foreach($msk as $kiosk=>$v)
	{

		$id = $v['id'];
		$hpd = $v['hoursPerDay'];
		$name = $v['name'];
		$region = $v['region_id'];
		if($region==1)
		{
			$region = "Москва";
		}
		if($region==2)
		{
			$region = "СПБ";
		}if($region==3)
	{
		$region = "НН";
	}
		if($region==4)
		{
			$region = "КЗН";
		}
		if($region==5)
		{
			$region = "Урал";
		}
		if($region==6)
		{
			$region = "ЮГ";
		}
		if($region==7)
		{
			$region = "Сибирь";
		}
		if($region==8)
		{
			$region = "ДВ";
		}
		if($region==11)
		{
			$region = "ЦР";
		}
		$pdo = new PDO('pgsql:host=10.0.3.194;port=5432;dbname=kiosk_srv', 'kiosk_adm', '@gg3n1n');
		sleep(5);
		$stmt = $pdo->prepare("SELECT pings_count, created, kiosk_id
        FROM kiosk_day_log
               WHERE created>date('2014-01-01')AND kiosk_day_log.created<date('2015-01-01') AND kiosk_id = $id
 			");

		$stmt->execute();
		$pingsCountDate  = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$pdo= null;
		foreach($pingsCountDate as $key2 => $val2)
		{
			$idealPings = 12*$hpd;
			$lost = $idealPings - $val2['pings_count'];
			$pingsCreated = array(
				'id'=>$val2['kiosk_id'],
				'name' => $name,
				'created'=>$val2['created'],
				'pings_count'=>$val2['pings_count'],
				'pings lost'=> $lost,
				'hoursPerDay' => $hpd,
				'region' =>iconv("utf-8", "windows-1251", $region)
			);
			fputcsv($handle, $pingsCreated,";");

		}
	}


	fputcsv($handle, array('spb'=>'spb'),";");
	foreach($spb as $kiosk=>$v)
	{

		$id = $v['id'];
		$hpd = $v['hoursPerDay'];
		$name = $v['name'];
		$region = $v['region_id'];
		if($region==1)
		{
			$region = "Москва";
		}
		if($region==2)
		{
			$region = "СПБ";
		}if($region==3)
	{
		$region = "НН";
	}
		if($region==4)
		{
			$region = "КЗН";
		}
		if($region==5)
		{
			$region = "Урал";
		}
		if($region==6)
		{
			$region = "ЮГ";
		}
		if($region==7)
		{
			$region = "Сибирь";
		}
		if($region==8)
		{
			$region = "ДВ";
		}
		if($region==11)
		{
			$region = "ЦР";
		}

		$pdo = new PDO('pgsql:host=10.0.3.194;port=5432;dbname=kiosk_srv', 'kiosk_adm', '@gg3n1n');
		sleep(5);
		$stmt = $pdo->prepare("SELECT pings_count, created, kiosk_id
        FROM kiosk_day_log
               WHERE created>date('2014-01-01')AND kiosk_day_log.created<date('2015-01-01') AND kiosk_id = $id
 			");

		$stmt->execute();
		$pingsCountDate  = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$pdo=null;
		foreach($pingsCountDate as $key2 => $val2)
		{
			$idealPings = 12*$hpd;
			$lost = $idealPings - $val2['pings_count'];
			$pingsCreated = array(
				'id'=>$val2['kiosk_id'],
				'name' => $name,
				'created'=>$val2['created'],
				'pings_count'=>$val2['pings_count'],
				'pings lost'=> $lost,
				'hoursPerDay' => $hpd,
				'region' =>iconv("utf-8", "windows-1251", $region)
			);
			fputcsv($handle, $pingsCreated,";");

		}
	}

	fputcsv($handle, array('nn'=>'nn'),";");
	foreach($nn as $kiosk=>$v)
	{

		$id = $v['id'];
		$hpd = $v['hoursPerDay'];
		$name = $v['name'];
		$region = $v['region_id'];
		if($region==1)
		{
			$region = "Москва";
		}
		if($region==2)
		{
			$region = "СПБ";
		}if($region==3)
	{
		$region = "НН";
	}
		if($region==4)
		{
			$region = "КЗН";
		}
		if($region==5)
		{
			$region = "Урал";
		}
		if($region==6)
		{
			$region = "ЮГ";
		}
		if($region==7)
		{
			$region = "Сибирь";
		}
		if($region==8)
		{
			$region = "ДВ";
		}
		if($region==11)
		{
			$region = "ЦР";
		}

		$pdo = new PDO('pgsql:host=10.0.3.194;port=5432;dbname=kiosk_srv', 'kiosk_adm', '@gg3n1n');
		sleep(5);
		$stmt = $pdo->prepare("SELECT pings_count, created, kiosk_id
        FROM kiosk_day_log
               WHERE created>date('2014-01-01')AND kiosk_day_log.created<date('2015-01-01') AND kiosk_id = $id
 			");

		$stmt->execute();
		$pingsCountDate  = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$pdo=null;
		foreach($pingsCountDate as $key2 => $val2)
		{
			$idealPings = 12*$hpd;
			$lost = $idealPings - $val2['pings_count'];
			$pingsCreated = array(
				'id'=>$val2['kiosk_id'],
				'name' => $name,
				'created'=>$val2['created'],
				'pings_count'=>$val2['pings_count'],
				'pings lost'=> $lost,
				'hoursPerDay' => $hpd,
				'region' =>iconv("utf-8", "windows-1251", $region)
			);
			fputcsv($handle, $pingsCreated,";");

		}
	}
	fputcsv($handle, array('kzn'=>'kzn'),";");
	foreach($kzn as $kiosk=>$v)
	{


		$id = $v['id'];
		$hpd = $v['hoursPerDay'];
		$name = $v['name'];
		$region = $v['region_id'];
		if($region==1)
		{
			$region = "Москва";
		}
		if($region==2)
		{
			$region = "СПБ";
		}if($region==3)
	{
		$region = "НН";
	}
		if($region==4)
		{
			$region = "КЗН";
		}
		if($region==5)
		{
			$region = "Урал";
		}
		if($region==6)
		{
			$region = "ЮГ";
		}
		if($region==7)
		{
			$region = "Сибирь";
		}
		if($region==8)
		{
			$region = "ДВ";
		}
		if($region==11)
		{
			$region = "ЦР";
		}


		$pdo = new PDO('pgsql:host=10.0.3.194;port=5432;dbname=kiosk_srv', 'kiosk_adm', '@gg3n1n');
		sleep(5);
		$stmt = $pdo->prepare("SELECT pings_count, created, kiosk_id
        FROM kiosk_day_log
               WHERE created>date('2014-01-01')AND kiosk_day_log.created<date('2015-01-01') AND kiosk_id = $id
 			");

		$stmt->execute();
		$pingsCountDate  = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$pdo=null;
		foreach($pingsCountDate as $key2 => $val2)
		{
			$idealPings = 12*$hpd;
			$lost = $idealPings - $val2['pings_count'];
			$pingsCreated = array(
				'id'=>$val2['kiosk_id'],
				'name' => $name,
				'created'=>$val2['created'],
				'pings_count'=>$val2['pings_count'],
				'pings lost'=> $lost,
				'hoursPerDay' => $hpd,
				'region' =>iconv("utf-8", "windows-1251", $region)
			);
			fputcsv($handle, $pingsCreated,";");

		}
	}
	fputcsv($handle, array('ural'=>'ural'),";");
	foreach($ural as $kiosk=>$v)
	{


		$id = $v['id'];
		$hpd = $v['hoursPerDay'];
		$name = $v['name'];
		$region = $v['region_id'];
		if($region==1)
		{
			$region = "Москва";
		}
		if($region==2)
		{
			$region = "СПБ";
		}if($region==3)
	{
		$region = "НН";
	}
		if($region==4)
		{
			$region = "КЗН";
		}
		if($region==5)
		{
			$region = "Урал";
		}
		if($region==6)
		{
			$region = "ЮГ";
		}
		if($region==7)
		{
			$region = "Сибирь";
		}
		if($region==8)
		{
			$region = "ДВ";
		}
		if($region==11)
		{
			$region = "ЦР";
		}


		$pdo = new PDO('pgsql:host=10.0.3.194;port=5432;dbname=kiosk_srv', 'kiosk_adm', '@gg3n1n');
		sleep(5);
		$stmt = $pdo->prepare("SELECT pings_count, created, kiosk_id
        FROM kiosk_day_log
               WHERE created>date('2014-01-01')AND kiosk_day_log.created<date('2015-01-01') AND kiosk_id = $id
 			");

		$stmt->execute();
		$pingsCountDate  = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$pdo=null;
		foreach($pingsCountDate as $key2 => $val2)
		{
			$idealPings = 12*$hpd;
			$lost = $idealPings - $val2['pings_count'];
			$pingsCreated = array(
				'id'=>$val2['kiosk_id'],
				'name' => $name,
				'created'=>$val2['created'],
				'pings_count'=>$val2['pings_count'],
				'pings lost'=> $lost,
				'hoursPerDay' => $hpd,
				'region' =>iconv("utf-8", "windows-1251", $region)
			);
			fputcsv($handle, $pingsCreated,";");

		}
	}

	fputcsv($handle, array('ugl'=>'ug'),";");
	foreach($ug as $kiosk=>$v)
	{


		$id = $v['id'];
		$hpd = $v['hoursPerDay'];
		$name = $v['name'];
		$region = $v['region_id'];
		if($region==1)
		{
			$region = "Москва";
		}
		if($region==2)
		{
			$region = "СПБ";
		}if($region==3)
	{
		$region = "НН";
	}
		if($region==4)
		{
			$region = "КЗН";
		}
		if($region==5)
		{
			$region = "Урал";
		}
		if($region==6)
		{
			$region = "ЮГ";
		}
		if($region==7)
		{
			$region = "Сибирь";
		}
		if($region==8)
		{
			$region = "ДВ";
		}
		if($region==11)
		{
			$region = "ЦР";
		}


		$pdo = new PDO('pgsql:host=10.0.3.194;port=5432;dbname=kiosk_srv', 'kiosk_adm', '@gg3n1n');
		sleep(5);
		$stmt = $pdo->prepare("SELECT pings_count, created, kiosk_id
        FROM kiosk_day_log
               WHERE created>date('2014-01-01')AND kiosk_day_log.created<date('2015-01-01') AND kiosk_id = $id
 			");

		$stmt->execute();
		$pingsCountDate  = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$pdo=null;
		foreach($pingsCountDate as $key2 => $val2)
		{
			$idealPings = 12*$hpd;
			$lost = $idealPings - $val2['pings_count'];
			$pingsCreated = array(
				'id'=>$val2['kiosk_id'],
				'name' => $name,
				'created'=>$val2['created'],
				'pings_count'=>$val2['pings_count'],
				'pings lost'=> $lost,
				'hoursPerDay' => $hpd,
				'region' =>iconv("utf-8", "windows-1251", $region)
			);
			fputcsv($handle, $pingsCreated,";");

		}
	}

	fputcsv($handle, array('sib'=>'sib'),";");
	foreach($sib as $kiosk=>$v)
	{


		$id = $v['id'];
		$hpd = $v['hoursPerDay'];
		$name = $v['name'];
		$region = $v['region_id'];
		if($region==1)
		{
			$region = "Москва";
		}
		if($region==2)
		{
			$region = "СПБ";
		}
		if($region==3)
		{
		$region = "НН";
		}
		if($region==4)
		{
			$region = "КЗН";
		}
		if($region==5)
		{
			$region = "Урал";
		}
		if($region==6)
		{
			$region = "ЮГ";
		}
		if($region==7)
		{
			$region = "Сибирь";
		}
		if($region==8)
		{
			$region = "ДВ";
		}
		if($region==11)
		{
			$region = "ЦР";
		}


		$pdo = new PDO('pgsql:host=10.0.3.194;port=5432;dbname=kiosk_srv', 'kiosk_adm', '@gg3n1n');
		sleep(5);
		$stmt = $pdo->prepare("SELECT pings_count, created, kiosk_id
        FROM kiosk_day_log
               WHERE created>date('2014-01-01')AND kiosk_day_log.created<date('2015-01-01') AND kiosk_id = $id
 			");

		$stmt->execute();
		$pingsCountDate  = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$pdo=null;
		foreach($pingsCountDate as $key2 => $val2)
		{
			$idealPings = 12*$hpd;
			$lost = $idealPings - $val2['pings_count'];
			$pingsCreated = array(
				'id'=>$val2['kiosk_id'],
				'name' => $name,
				'created'=>$val2['created'],
				'pings_count'=>$val2['pings_count'],
				'pings lost'=> $lost,
				'hoursPerDay' => $hpd,
				'region' =>iconv("utf-8", "windows-1251", $region)
			);
			fputcsv($handle, $pingsCreated,";");

		}
	}

	fputcsv($handle, array('dv'=>'dv'),";");
	foreach($dv as $kiosk=>$v)
	{


		$id = $v['id'];
		$hpd = $v['hoursPerDay'];
		$name = $v['name'];
		$region = $v['region_id'];
		if($region==1)
		{
			$region = "Москва";
		}
		if($region==2)
		{
			$region = "СПБ";
		}if($region==3)
	{
		$region = "НН";
	}
		if($region==4)
		{
			$region = "КЗН";
		}
		if($region==5)
		{
			$region = "Урал";
		}
		if($region==6)
		{
			$region = "ЮГ";
		}
		if($region==7)
		{
			$region = "Сибирь";
		}
		if($region==8)
		{
			$region = "ДВ";
		}
		if($region==11)
		{
			$region = "ЦР";
		}


		$pdo = new PDO('pgsql:host=10.0.3.194;port=5432;dbname=kiosk_srv', 'kiosk_adm', '@gg3n1n');
		sleep(5);
		$stmt = $pdo->prepare("SELECT pings_count, created, kiosk_id
        FROM kiosk_day_log
               WHERE created>date('2014-01-01')AND kiosk_day_log.created<date('2015-01-01') AND kiosk_id = $id
 			");

		$stmt->execute();
		$pingsCountDate  = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$pdo=null;
		foreach($pingsCountDate as $key2 => $val2)
		{
			$idealPings = 12*$hpd;
			$lost = $idealPings - $val2['pings_count'];
			$pingsCreated = array(
				'id'=>$val2['kiosk_id'],
				'name' => $name,
				'created'=>$val2['created'],
				'pings_count'=>$val2['pings_count'],
				'pings lost'=> $lost,
				'hoursPerDay' => $hpd,
				'region' =>iconv("utf-8", "windows-1251", $region)
			);
			fputcsv($handle, $pingsCreated,";");

		}
	}
	fputcsv($handle, array('cr'=>'cr'),";");
	foreach($cr as $kiosk=>$v)
	{


		$id = $v['id'];
		$hpd = $v['hoursPerDay'];
		$name = $v['name'];
		$region = $v['region_id'];
		if($region==1)
		{
			$region = "Москва";
		}
		if($region==2)
		{
			$region = "СПБ";
		}if($region==3)
	{
		$region = "НН";
	}
		if($region==4)
		{
			$region = "КЗН";
		}
		if($region==5)
		{
			$region = "Урал";
		}
		if($region==6)
		{
			$region = "ЮГ";
		}
		if($region==7)
		{
			$region = "Сибирь";
		}
		if($region==8)
		{
			$region = "ДВ";
		}
		if($region==11)
		{
			$region = "ЦР";
		}


		$pdo = new PDO('pgsql:host=10.0.3.194;port=5432;dbname=kiosk_srv', 'kiosk_adm', '@gg3n1n');
		sleep(5);
		$stmt = $pdo->prepare("SELECT pings_count, created, kiosk_id
        FROM kiosk_day_log
               WHERE created>=date('2014-01-01')AND kiosk_day_log.created<date('2015-01-01') AND kiosk_id = $id
 			");

		$stmt->execute();
		$pingsCountDate  = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$pdo=null;
		foreach($pingsCountDate as $key2 => $val2)
		{
			$idealPings = 12*$hpd;
			$lost = $idealPings - $val2['pings_count'];
			$pingsCreated = array(
				'id'=>$val2['kiosk_id'],
				'name' => $name,
				'created'=>$val2['created'],
				'pings_count'=>$val2['pings_count'],
				'pings lost'=> $lost,
				'hoursPerDay' => $hpd,
				'region' =>iconv("utf-8", "windows-1251", $region)
			);
			fputcsv($handle, $pingsCreated,";");

		}
	}
	fclose($handle);









