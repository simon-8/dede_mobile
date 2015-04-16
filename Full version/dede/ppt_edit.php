<?php
require(dirname(__FILE__)."/config.php");
CheckPurview('plus_幻灯管理');
require_once(DEDEINC.'/typelink.class.php');
if(empty($dopost))
{
	$dopost = '';
}

$aid = ereg_replace('[^0-9]','',$aid);
$ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? "ppt_main.php" : $_COOKIE['ENV_GOBACK_URL'];

if($dopost=='delete')
{
	$dsql->ExecuteNoneQuery("Delete From `#@__myppt` where aid='$aid' ");
	ShowMsg("成功删除幻灯片！",$ENV_GOBACK_URL);
	exit();
}
else if($dopost=='saveedit')
{
	$starttime = GetMkTime($starttime);
	$endtime = GetMkTime($endtime);
	$query = "Update `#@__myppt`
	 set
	 orderid='$orderid',
	 title='$title',
	 typeid='$typeid',
	 pic='$picname',
	 url='$url'
	 where aid='$aid'
	 ";
	$dsql->ExecuteNoneQuery($query);
	ShowMsg("成功更改幻灯片！",$ENV_GOBACK_URL);
	exit();
}

$row = $dsql->GetOne("Select * From `#@__myppt` where aid='$aid'");
$dsql->Execute('dd','SELECT * FROM `#@__myppttype` ORDER BY id DESC');
$option = '';
while($arr = $dsql->GetArray('dd'))
{
    if ($arr['id'] == $row['typeid'])
    {
        $option .= "<option value='{$arr['id']}' selected='selected'>{$arr['typename']}</option>\n\r";
    } else {
        $option .= "<option value='{$arr['id']}'>{$arr['typename']}</option>\n\r";
    }
}
include DedeInclude('templets/ppt_edit.htm');

?>