<?php
/*
 * Plugin Name: 腾讯007验证码
 * Version: 1.0
 * Description: 登录使用腾讯007验证码
 * Plugin URI: https://www.coderecord.cn
 * Author: 702018304@qq.com
 * Author URI: https://www.coderecord.cn
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: tcaptcha
*/
$option = get_option("tcaptcha");
if($option['enabled'] == "1"){
	add_action('login_form','tcaptcha_login_fields',1);
	add_action('login_form_login','tcaptcha_login_check',1);
}
//登录页面添加007验证码的内容
function tcaptcha_login_fields() {
        $option = get_option("tcaptcha");
		echo "
        <script src='https://ssl.captcha.qq.com/TCaptcha.js'></script>
        <p>
            <label for='math' class='small'>腾讯007人机验证</label>
            <br />
            <input type='hidden' name='tcaptcha_007' value='1' />
            <input type='hidden' name='tcaptcha_ticket' id='tcaptcha_ticket' value='' />
            <input type='hidden' name='tcaptcha_randstr' id='tcaptcha_randstr' value='' />
            <input type='button' class='button button-primary' id='TencentCaptcha'  data-appid='".$option["appid"]."'  data-cbfn='tcaptcha_callback' value='点我验证' style='float:none;width:100%' />
        </p><br/>
        <script>
            window.tcaptcha_callback = function(res){
                console.log(res)
                if(res.ret === 0){
                    document.getElementById('tcaptcha_ticket').value= res.ticket;
                    document.getElementById('tcaptcha_randstr').value=res.randstr;
                    document.getElementById('TencentCaptcha').value='您已通过验证~';
                }else{
                    alert('您已取消验证');
                }
            }
        </script>
        ";
}

//post提交验证逻辑
function tcaptcha_login_check() {
    if(!isset($_POST['tcaptcha_007']))
		return;
	$option = get_option("tcaptcha");
	if($option['enabled'] != 1)
		return;
	
    if(!isset($_POST['tcaptcha_ticket']) || !isset($_POST['tcaptcha_randstr'])){
        wp_die('错误: 缺少腾讯007验证码参数');
        return;
    }
    
	$ticket=$_POST['tcaptcha_ticket'];
	$randstr = $_POST['tcaptcha_randstr'];
	
    $data = [
        "aid"=>$option["appid"],
        "AppSecretKey"=>$option["appkey"],
        "Ticket"=>$ticket,
        "Randstr"=>$randstr,
        "UserIP"=>$_SERVER["REMOTE_ADDR"]
    ];
    
    
    $url = "https://ssl.captcha.qq.com/ticket/verify?".http_build_query($data);
    $result = file_get_contents($url);
    $result = json_decode($result,true);
    if($result["response"] != 1){
        wp_die("错误：腾讯007验证失败，原因代码：".$result["err_msg"]);
    }
}



//后台登录初始化
add_action('admin_init', 'tcaptcha_admin_init', 1);
function tcaptcha_admin_init() {
	$option = get_option("tcaptcha");
    $GLOBALS["tcaptcha_enabled"] = $option['enabled'];
	$GLOBALS["tcaptcha_appid"] = $option['appid'];
	$GLOBALS["tcaptcha_appkey"] = $option["appkey"];
	
	register_setting('tcaptcha_admin_options_group', 'tcaptcha');
}


//后台的插件配置页面
add_action('admin_menu', 'tcaptcha_admin_menu');
function tcaptcha_admin_menu() {
    add_options_page("腾讯007验证码", "腾讯007验证码", 'manage_options', 'tcaptcha', 'tcaptcha_options_page');
}

function tcaptcha_options_page(){
	?>
	    <div class="wrap">
        <h2>腾讯007验证码配置</h2>
        <form action="options.php" method="post">
        <?php settings_fields('tcaptcha_admin_options_group'); ?>
        <table class="form-table">
			<tr valign="top">
				<th scope="row">启用</th>
				<td>
					<label><input name="tcaptcha[enabled]" type="checkbox" value="1"  <?php checked($GLOBALS["tcaptcha_enabled"],1);?>/>
					登录时启用腾讯007验证码</label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">APPID</th>
				<td>
					<input name="tcaptcha[appid]" size="60" placeholder="" value="<?php echo $GLOBALS["tcaptcha_appid"] ;?>" required />
					<p>APPID获取方式：https://007.qq.com/captcha/#/gettingStart</p>
				</td>
			</tr>
            <tr valign="top">
				<th scope="row">APPKEY</th>
				<td>
					<input name="tcaptcha[appkey]" size="60" placeholder="" value="<?php echo $GLOBALS["tcaptcha_appkey"] ;?>" required />
					<p>APPKEY获取方式：https://007.qq.com/captcha/#/gettingStart</p>
				</td>
			</tr>
      </table>
        <?php submit_button();?>
		</form>
    </div>
	
<?php
}
?>