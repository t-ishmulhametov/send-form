<?php

/*

 	$this->return=101 - msg send successful
	$this->return=100 - msg send not successful
	$this->return=50 - input captcha not successful
	$this->return=51 - you need show captcha image
	$this->return=52 - this msg is send

 */

class sendPost{
	var $c;
	var $dm;
	var $return;
	function __construct($a){
		$this->c=$a;
		$this->dm=$a["path-for-save-msg"];
		$this->msg=count(scandir($this->dm))-2;
		$this->remove_old_msg();
	}

	function __destruct(){
		echo $this->return;
	}

	private function remove_old_msg(){

		$files=scandir($this->dm);

		foreach ($files as $file){

			if ($file!=='.' && $file!=='..'){

				$f=$this->dm.$file;
				if (file_exists($f)) {

					$t=strtotime('now')-filemtime($f);
					if ($t>300){
						unlink($f);
					}

				}

			}

		}

	}

	private function check_msg($msg){

		$files=scandir($this->dm);
		$r=true;
		foreach ($files as $file){

			if ($file!=='.' && $file!=='..'){

				$f=$this->dm.$file;
				$s_msg=file_get_contents($f);

				if ($s_msg==$msg){
					$r=false;
				}

			}

		}

		return $r;
	}

	public function send_form_to_mail($post){
		$send=false;
		if ($this->msg<$this->c["length-msg-in-10-seconds-for-enable-captcha"]){
			$send=true;
		}else if (!isset($_COOKIE[$this->c["captcha"]["name-cookie-for-save-sig"]]) && trim($_COOKIE[$this->c["captcha"]["name-cookie-for-save-sig"]])==''){
			$this->return=50;
		}else if ($this->sig($post["captcha"])){
			$send=true;
		}else{
			$this->return=51;
		}

		if ($send){
			$msg='';
			foreach ($post["value"] as $val){
				$msg.="<b>".htmlspecialchars($val[0])."</b> ".htmlspecialchars($val[1])."<br>";
			}
			$post["title"]=htmlspecialchars($post["title"]);

			if ($msg!=='' && $post["title"]!==''){

				$base64=base64_encode($msg.$post["title"]);

				if ($this->check_msg($base64)){

					file_put_contents($this->dm.strtotime("now"),$base64);

					$this->send_mail($post["title"],$msg);

				}else{
					$this->return=52;
				}
			}
		}

	}

	public function send_mail($title,$msg){

		$to = implode(",",$this->c["mail"]);

		$headers = "From: Robot <no-reply@".$_SERVER["HTTP_HOST"].">\r\nContent-type: text/html; charset=".$this->c["charset"]." \r\n";
		if (mail ($to, $title.' ['.$_SERVER["HTTP_HOST"].']', $msg, $headers)){
			$this->return=101;
		}else{
			$this->return=100;
		}

	}

	public function generate_pic(){
			$conf=$this->c["captcha"];
			$src = imagecreatetruecolor($conf['width'],$conf['height']);
			$fon = imagecolorallocate($src,255,255,255);
			imagefill($src,0,0,$fon);
			for($i=0;$i < $conf["length-letter-for-background"];$i++)
			{
			$color = imagecolorallocatealpha($src,rand(0,255),rand(0,255),rand(0,255),100);
			$letter = $conf["list-letters"][rand(0,sizeof($conf["list-letters"])-1)];
			$size = rand($conf["font-size"]-2,$conf["font-size"]+2);
			imagettftext($src,$size,rand(0,45),
					rand($conf['width']*0.1,$conf['width']-$conf['width']*0.1),
					rand($conf['height']*0.2,$conf['height']),$color,$conf["font-config"]["font-dir"].$conf["font-config"]["list-fonts"][rand(0,(sizeof($conf["font-config"]["list-fonts"])-1))],$letter);
			}
			for($i=0;$i < $conf["length-letter"];$i++)
			{
					$color = imagecolorallocatealpha($src,$conf["color-letter"][rand(0,sizeof($conf["color-letter"])-1)],
					$conf["color-letter"][rand(0,sizeof($conf["color-letter"])-1)],
					$conf["color-letter"][rand(0,sizeof($conf["color-letter"])-1)],rand(20,40));
					$letter = $conf["list-letters"][rand(0,sizeof($conf["list-letters"])-1)];
					$size = rand($conf["font-size"]*2-2,$conf["font-size"]*2+2);
					$x = ($i+1)*($conf["font-size"]+8) + rand(($i+1),$conf["length-letter"]);
					$y = (($conf['height']*2)/3) + rand(0,5);
					$cod[] = $letter;
					imagettftext($src,$size,rand(10,30),$x,$y,$color,$conf["font-config"]["font-dir"].$conf["font-config"]["list-fonts"][rand(0,(sizeof($conf["font-config"]["list-fonts"])-1))],$letter);
			}

			$cod = implode("",$cod);
			$this->set_sig($cod);
			header ("Content-type: image/gif");
			imagegif($src);
	}

    public function dump($a){echo '<pre>';var_dump($a);echo '</pre>';}

    private function set_sig($s){
    	$s=strtolower($s);
		$sole=$this->c["captcha"]["sole"];
    	$s=crypt($s,$sole);
    	setcookie($this->c["captcha"]["name-cookie-for-save-sig"], $s, time() + 20, "/");
    }

    public function sig($s){
    	$sole=$this->c["captcha"]["sole"];
    	$s=crypt($s,$sole);
    	return $_COOKIE[$this->c["captcha"]["name-cookie-for-save-sig"]]==$s?true:false;
    }
}


/*
 * space for work with sendPost class
 *
 */

$s=new sendPost(Array(
					"path-for-save-msg"=>"msg/",
					"mail"=>array("robot@example.ru"),
					"charset"=>"utf-8",
					"length-msg-in-10-seconds-for-enable-captcha"=>10,
					"captcha"=> Array(
										"name-cookie-for-save-sig"=>"sigForm",
										"sole"=>"!Uro@OiePp322",
										"width"=>160,
										"height"=>80,
										"font-size"=>18,
										"length-letter"=> 4,
										"length-letter-for-background"=>10,
										"color-letter"=> array("90","110","130","150","170","190","210"),
										"list-letters"=>array("a","b","c","d","e","f","g","q","w","m","p","z","x"),
										"font-config"=>Array(
																"font-dir"=>"ajync/",
																"list-fonts"=>Array("6786DaMe.ttf")
															)

									 )
			));
function dump($a){echo '<pre>';var_dump($a);echo '</pre>';}
if (intval($_GET['act'])==51){
	$s->generate_pic();
}
else{

	$value=Array();
	foreach ($_POST as $k=>$v){
		if ($k!=="title" && $k!=="captcha"){
			$value[]=Array($k,$v);
		}
	}

	$s->send_form_to_mail(Array(
							"title"=>$_POST['title'],
							"value"=>$value,
							"captcha"=>strtolower($_POST['captcha'])
						));

}



?>
