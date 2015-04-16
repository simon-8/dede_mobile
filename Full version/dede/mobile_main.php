<?php
require_once(dirname(__FILE__).'/config.php');
$dsql->Execute();
$pre_= $GLOBALS['cfg_dbprefix']; //获得当前表前缀
mysql_query("UPDATE dede_wapconfig SET value='".$pre_."' WHERE var='prefix'");//更新下表前缀

if($_GET['change']){
		echo clean_catid();
		exit();
}
if(!empty($_POST)){
	// $typeid = implode(',', $_POST['categoryid']);//checkbox的值  转成字符串 例 2,1,4,5
	$typeid = $_POST['categoryid'];//首页栏目
	$typearr = implode(',', $_POST['cate']);//需要显示的栏目 转成字符串
    $about_id = $_POST['about'];//关于我们栏目id
    $news_id = $_POST['news'];//首页公告区id
	$mobile = $_POST['dede_mobile'];//电话
	$com = $_POST['dede_com'];//公司名
	$addr = $_POST['dede_addr'];//公司地址
	$map = $_POST['map'];//公司地图
	$email = $_POST['email'];//公司邮箱
	$r = '';
/*	echo "<pre>";
	print_r($_POST);*/
	
    /*需要显示的栏目  表dede_wapchannel*/
    $clear = mysql_query("DELETE FROM  dede_wapchannel");//清除表
	foreach ($typeid as $k => $v) {
        $up = mysql_query("INSERT INTO dede_wapchannel (typeid,mobile) values ('$v','1')");
	}
			
	/*添加图片和排序 waptype表*/
	$clear2 = mysql_query("DELETE FROM dede_waptype");//清除表
	 foreach ($_POST as $key => $value) {
	 	if(strstr($key, 'typelitpic_')){
	 		//是图片 typelitpic_12 使用str_replace得出id
	 		$id = str_replace('typelitpic_','',$key);
	 		$orderstr = 'order_'.$id;
	 		$order = isset($_POST[$orderstr]) ? $_POST[$orderstr] : 1 ;
/*			$zd = $dsql->GetOne("SELECT * FROM dede_waptype WHERE typeid=".$id);
			//检查有没有该项配置
			//有则修改  无则添加
			if($zd){
				$sql = "UPDATE dede_waptype SET typelitpic='".$value."',listorder=".$order." WHERE typeid=".$id;
			}else{
				$sql = "INSERT INTO dede_waptype (typeid,typelitpic,listorder) values('".$id."','".$value."',".$order.")";
			}*/
			$sql = "INSERT INTO dede_waptype (typeid,typelitpic,listorder) values('".$id."','".$value."',".$order.")";
	 		$img = mysql_query($sql);
	 		if(!$img){	echo mysql_error();$r = false;	}else{	$r = true;	}
	 	}
	 }
/*    $aid = mysql_query("UPDATE dede_wapconfig SET value=$about_id WHERE var='about'");*/
	$dede_mobile = mysql_query("UPDATE dede_wapconfig SET value='$mobile' WHERE var='mobile'");
	$dede_com = mysql_query("UPDATE  dede_wapconfig SET value='$com' WHERE var='company'");
	$dede_addr = mysql_query("UPDATE dede_wapconfig SET value='$addr' WHERE var='address'");
	$map_up = mysql_query("UPDATE dede_wapconfig SET value='$map' WHERE var='map'");
    $about_up = mysql_query("UPDATE dede_wapconfig  SET value='$about_id' WHERE var='about'");
    $email_up = mysql_query("UPDATE dede_wapconfig  SET value='$email' WHERE var='email'");
    $cate_up = mysql_query("UPDATE dede_wapconfig SET value='".$typearr."' WHERE var='typeid'");
    $news_up = mysql_query("UPDATE dede_wapconfig SET value='".$news_id."' WHERE var='news'");
	$r ? ShowMsg('修改成功','mobile_main.php',0,1) : ShowMsg('修改失败 , 请留意错误代码 .','mobile_main.php',0,4);
}else{

    //调用系统分类 并确定有没有被选中  dede_wapchannel表
	$category1 = get_query("SELECT id,typename FROM  ".$pre_."arctype WHERE reid=0 AND topid=0");
//	var_dump($category1);
	$typearr = explode(',',get_config("typeid"));//被选中id

	//使用dede_waptype内设置的图片
	$category = array();
		foreach ($category1 as $key => $value) {
			$value['typelitpic'] = get_pic($value['id']);//追加图片字段
			$value['listorder'] = get_value($value['id']);//追加排序
            $value['mobile'] = get_mobile($value['id']);//追加mobile字段
			$category[] = $value;
		}
	$contact = array();
	$contact['typelitpic'] = get_pic(88);
	$contact['id'] = 88;
	$contact['mobile'] = 1;
	$contact['typename'] = '联系我们';
	$contact['listorder'] = get_value(88);
	$category[] = $contact;
	$tools = array();
	$tools['typelitpic'] = get_pic(99);
	$tools['id'] = 99;
	$tools['mobile'] = 1;
	$tools['typename'] = '实用工具';
	$tools['listorder'] = get_value(99);
	$category[] = $tools;
	$dede_mobile = get_config('mobile');
	$dede_addr = get_config('address');
	$dede_com = get_config('company');
	$map = get_config('map');
	$email = get_config('email');
    $about_id = get_config("about");
    $about_name = get_typename($about_id);
    // $about_name = $about_name['typename'];
    $news_id = get_config('news');
    $news_name = get_typename($news_id);
/*    if( $about_id == 88 ){
        $about_name = "联系我们";
    }elseif( $about_id == 99 ){
        $about_name = "实用工具";
    }else{*/

/*    }*/
	// print_r($category);
	include DedeInclude('templets/mobile_main.htm');
}


//批量查询
function get_query($sql){
		$result = mysql_query($sql);
		$list = array();
		while($result1 = mysql_fetch_assoc($result)){
			$list[] = $result1;
		}
		return $list;
}
//读取配置值
function get_config($varname){
	$sql = "SELECT value FROM dede_wapconfig WHERE var='$varname'";
	$result = mysql_query($sql);
	$result1 = mysql_fetch_assoc($result);
	$value = $result1['value'];
	return $value;
}
function get_pic($typeid){
	$sql = "SELECT typeid,typelitpic FROM dede_waptype WHERE typeid=".$typeid;
	$result = mysql_query($sql);
	$result1 = mysql_fetch_assoc($result);
	$value = $result1['typelitpic'];
	return $value;
}
function get_value($typeid){
	$sql = "SELECT listorder FROM dede_waptype WHERE typeid=".$typeid;
	$result = mysql_query($sql);
	$result1 = mysql_fetch_assoc($result);
	$value = $result1['listorder'] ? $result1['listorder'] : 0 ;
	return $value;
}
function get_mobile($typeid){
    global $dsql;
    $sql = "SELECT * FROM dede_wapchannel WHERE typeid=".$typeid;
    $result1 = $dsql->GetOne($sql);
    $result = $result1 == '' ? 0 : $result1['mobile'];
    return $result;
}
function dede_config($varname){
	$sql = "SELECT value FROM  dede_wapconfig WHERE var='$varname'";
	$result = mysql_query($sql);
	$result1 = mysql_fetch_assoc($result);
	$value = $result1['value'];
	return $value;
}
function get_typename($typeid){
	global $dsql;
	$result = $dsql->GetOne("SELECT typename FROM `#@__arctype` WHERE id=".$typeid);
	return $result['typename'];
}
//二维数组按指定字段顺序排序
/*
 $array = array(
                array('id'=>'1','listorder'=>'4'),
                array('id'=>'2','listorder'=>'2')
);
order($array , 'listorder'); 按listorder字段排序
*/
function order($array,$zd){
	$arr = array();//设置个arr
	foreach ( $array as $key => $value ) {
                    /*----------------判断是不是数组------*/
                    $k = $value[$zd];  //用值做键
                    if( is_array($value )){
                        if( $arr[$k] != '' ){  // 6 6 5 4 3 31
                            //$k = $value[$zd]+1;避免重复
//                            $arr[$k] =
                        	$rand = $k+mt_rand(0,100);
                            $arr[$rand] = $value;
                        }else{

                    $arr[$k] = $value;  //
                }
		}
/*----------------判断是不是数组------*/
	}
			//arr组建完毕 , 开始排序
			ksort($arr);
	return $arr;
}

function clean_catid(){
	global $dsql,$pre_;
	$table = $pre_."arctype";
	/*
	顶级栏目 topid=0 reid=0
	二级栏目 reid=topid 同是顶级栏目
	三级栏目 reid topid不相同 reid是上级目录 topid应该是顶级栏目
	用reid<>topid做条件 查找错误的二级栏目和正确的三级栏目   检查topid是否存在  不存在就更正
	*/
	$count = '0';
	// $toparr = get_query("SELECT * FROM ".$table." WHERE reid=topid AND reid=0");
	$childarr = get_query("SELECT * FROM ".$table." WHERE reid<>topid");
	foreach ($childarr as $key => $value) {
		// $id = $value['id'];
		$parentid = $dsql->GetOne("SELECT * FROM ".$table." WHERE id=".$value['reid']);//检查reid是否是顶级栏目
		$reid = $parentid['reid'];//父元素的reid
		$topid = $parentid['topid'];//父元素的topid
		if( $reid == 0 and $topid ==0 ){ //父元素是顶级栏目  该栏目是二级栏目   设定topid = reid
			if($value['topid'] != $value['reid']){ //topid!=reid  说明数据有问题  修改
				$r = mysql_query("UPDATE ".$table." SET topid='".$value['reid']."' WHERE id=".$value['id']);
				$count += $r ? 1 : 0;				
			}
		}elseif( $reid == $topid and $reid <> 0){
			//父元素是二级栏目 该栏目是三级栏目  topid就该是二级栏目的reid  也就是顶级栏目id				
			if( $value['topid'] != $reid ){ 
				$r = mysql_query("UPDATE ".$table." SET topid='".$reid."' WHERE id=".$value['id']);
				$count += $r ? 1 : 0;				
			}
		}
	}
	return $count;
}



	function mysort($arr){
		$len = count($arr)-1;
		for($j=0;$j<$len;$j++){
			for($i=0;$i<$len-$j;$i++){
				if($arr[$i]>$arr[$i+1]){
					$k = $arr[$i];
					$arr[$i] = $arr[$i+1];
					$arr[$i+1] = $k;
				}
			}
		}
		return $arr;
	}
	
	function myorder($arr,$zd){
		$tmp = array();
		$tmp1 = array();
		foreach($arr as $v){
			$tmp[] = $v[$zd]; 
		}
		$tmp = mysort($tmp);

		foreach($tmp as $v){
			foreach($arr as $y){
				if($y[$zd] == $v){
					$tmp1[] = $y;
					break;
				}
			}
		}
		return $tmp1;
	}
?>