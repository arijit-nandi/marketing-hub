<?php
$CONFIG['system']['pathInclude'] = '../../';
include_once($CONFIG['system']['pathInclude'] . 'admin/config-admin.php');
$varSQL = getPostData();
$CONFIG['page'] = json_decode($varSQL['pageConfig'], true);
 

$str = '<select><option value="">' . $TEXT['All'] . '</option>';

$query = $CONFIG['dbconn']->prepare('
									SELECT ' . $CONFIG['db'][0]['prefix'] . 'system_timezones.id_tz as id,
										' . $CONFIG['db'][0]['prefix'] . 'system_timezones.timezone as term
									FROM ' . $CONFIG['db'][0]['prefix'] . 'system_timezones 
									WHERE ' . $CONFIG['db'][0]['prefix'] . 'system_timezones.del = (:nultime)
									ORDER BY ' . $CONFIG['db'][0]['prefix'] . 'system_timezones.timezone
									');
$query->bindValue(':nultime', '0000-00-00 00:00:00', PDO::PARAM_STR);
$query->execute();
$rows = $query->fetchAll(PDO::FETCH_ASSOC);
$num = $query->rowCount(); 

$aResult = array();
foreach($rows as $row){
	$row['term'] = (isset($TEXT[$row['term']])) ? $TEXT[$row['term']] : $row['term'];
	$aResult[$row['id']] = $row['term'];
}

//asort($aResult, SORT_NATURAL | SORT_FLAG_CASE);
foreach($aResult as $id => $term){
	$str .= '<option value="' . $id . '">' . $term . '</option>';
}

$str .= '</select>';
    
echo $str;

?>