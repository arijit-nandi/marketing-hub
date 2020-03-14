<?php
$CONFIG['system']['pathInclude'] = '../../';
include_once($CONFIG['system']['pathInclude'] . 'admin/config-admin.php'); 
$CONFIG['page'] = json_decode($_SERVER['HTTP_PAGE'], true);
$varSQL = getPostData(json_decode($_POST['data'], true));
include_once($CONFIG['system']['pathInclude'] . $CONFIG['system']['pathFunctionsAdmin'] . 'fu_sys-usercheck-function.php'); 


$date = new DateTime();
$now = $date->format('Y-m-d H:i:s');


$query = $CONFIG['dbconn']->prepare('
									SELECT ' . $CONFIG['db'][0]['prefix'] . 'system_tempdata.data,
										' . $CONFIG['db'][0]['prefix'] . 'system_tempdata.id_count,
										' . $CONFIG['db'][0]['prefix'] . 'system_tempdata.id_lang,
										' . $CONFIG['db'][0]['prefix'] . 'system_tempdata.id_dev,
										' . $CONFIG['db'][0]['prefix'] . 'system_tempdata.id_clid
									FROM ' . $CONFIG['db'][0]['prefix'] . 'system_tempdata 
									WHERE ' . $CONFIG['db'][0]['prefix'] . 'system_tempdata.id = (:id)
										AND ' . $CONFIG['db'][0]['prefix'] . 'system_tempdata.id_uid = (:uid)
										AND ' . $CONFIG['db'][0]['prefix'] . 'system_tempdata.modulname = (:modulname)
									');
$query->bindValue(':id', $varSQL['id'], PDO::PARAM_INT);
$query->bindValue(':uid', $CONFIG['USER']['id'], PDO::PARAM_INT);
$query->bindValue(':modulname', $CONFIG['page']['moduls'][$varSQL['modul']]['modulname'], PDO::PARAM_STR);
$query->execute();
$rows = $query->fetchAll(PDO::FETCH_ASSOC);
$num = $query->rowCount();

foreach($rows as $row){
	$aData = json_decode($row['data'], true);
	$aData['id_count'] = $row['id_count'];
	$aData['id_lang'] = $row['id_lang'];
	$aData['id_dev'] = $row['id_dev'];
	$aData['id_clid'] = $row['id_clid'];


	if($aData['password'] != ""){
		$t_hasher = new PasswordHash(8, false);
		$aData['password'] = $t_hasher->HashPassword($aData['password']);
	}  

	$query = $CONFIG['dbconn']->prepare('
										INSERT INTO ' . $CONFIG['db'][0]['prefix'] . 'system_user
										(
											firstname,
											lastname,
											email,
											id_r,											
											username,											
											password,	
											active_client,										
											create_at,    
											create_from, 
											change_from
										)
										VALUES
										(
											:firstname,
											:lastname,
											:email,
											:id_r,
											:username,
											:password,
											:active_client,										
											:create_at, 
											:create_from, 
											:create_from)
										');
	$query->bindValue(':firstname', $aData['firstname'], PDO::PARAM_STR);
	$query->bindValue(':lastname', $aData['lastname'], PDO::PARAM_STR);
	$query->bindValue(':email', $aData['email'], PDO::PARAM_STR);
	$query->bindValue(':id_r', $aData['id_r'], PDO::PARAM_INT);
	$query->bindValue(':username', $aData['username'], PDO::PARAM_STR);
	$query->bindValue(':password', $aData['password'], PDO::PARAM_STR);
	$query->bindValue(':active_client', $aData['id_clid'], PDO::PARAM_INT);
	$query->bindValue(':create_at', $now, PDO::PARAM_STR);
	$query->bindValue(':create_from', $CONFIG['USER']['id_real'], PDO::PARAM_INT);
	$query->execute();
	$idNew = $CONFIG['dbconn']->lastInsertId();

		
	$query = $CONFIG['dbconn']->prepare('
										INSERT INTO ' . $CONFIG['db'][0]['prefix'] . 'system_user2clients
										(id_uid, id_clid)
										VALUES
										(:id, :id_clid)
										');
	$query->bindValue(':id', $idNew, PDO::PARAM_INT);
	$query->bindValue(':id_clid', $aData['id_clid'], PDO::PARAM_INT);
	$query->execute();



}


#################################################

$query = $CONFIG['dbconn']->prepare('
									DELETE ' . $CONFIG['db'][0]['prefix'] . 'system_user2countries2languages
									FROM ' . $CONFIG['db'][0]['prefix'] . 'system_user2countries2languages 
									
									INNER JOIN ' . $CONFIG['db'][0]['prefix'] . 'sys_countries2languages
										ON ' . $CONFIG['db'][0]['prefix'] . 'system_user2countries2languages.id_count2lang = ' . $CONFIG['db'][0]['prefix'] . 'sys_countries2languages.id_count2lang
									
									WHERE ' . $CONFIG['db'][0]['prefix'] . 'system_user2countries2languages.id_uid = (:id_uid)
										AND ' . $CONFIG['db'][0]['prefix'] . 'sys_countries2languages.id_count2lang IN ('. implode(',', $CONFIG['USER']['count2lang']) . ')
									');
$query->bindValue(':id_uid', $varSQL['id'], PDO::PARAM_INT);
$query->execute();

if(isset($aData[0][0][0]['countries'])){
	foreach($aData[0][0][0]['countries'] as $val){
		$query = $CONFIG['dbconn']->prepare('
											INSERT INTO ' . $CONFIG['db'][0]['prefix'] . 'system_user2countries2languages
											(id_uid, id_count2lang)
											VALUES
											(:id_uid, :id_count2lang)
											');
		$query->bindValue(':id_uid', $idNew, PDO::PARAM_INT);
		$query->bindValue(':id_count2lang', $val, PDO::PARAM_INT);
		$query->execute();
	}
}



$out = array();
$out['id'] = $idNew;

echo json_encode($out);


?>