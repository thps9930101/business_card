<?php
	$id = isset($_GET["id"]) ? $_GET["id"] : "";
	/*if(empty($id)) {
		header('Location: https://www.google.com/');
		exit();
	}*/
	$name = "3D名片";
	$icon = "https://businesscard.lightmatrix3d.com/lightmatrix.png";
	$responseString = "";
	if(strlen($id) >= 20) {
		// API URL
		$url = "https://businesscard2.lightmatrix3d.com/api/getBC";
		// 要发送的数据
		$data = array(
			'public_id' => isset($_GET['id']) ? $_GET['id'] : ''
		);
		$jsonData = json_encode($data);
		// 初始化 cURL 会话
		$ch = curl_init($url);
		// 设置 cURL 选项
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($jsonData)
		));
		// 设置超时时间
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 请求最大执行时间
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 连接最大时间
		// 执行 cURL 请求并获取响应
		$response = curl_exec($ch);
		// 检查是否有错误
		if ($response === false) {
			//$error = curl_error($ch);
			//$response = "ERROR";
			//echo "cURL Error: $error";
			$responseString = '{"success":false}';
		} else {
			// 解析并处理响应
			$responseData = json_decode($response, true);
			if(isset($responseData)) {
				if(isset($responseData[0])) $responseData = $responseData[0];
				$responseString = json_encode($responseData);
				if(isset($responseData["success"]) && $responseData["success"] && isset($responseData["message"])) {
					$message = $responseData["message"];
					if(isset($message["release_name"]) && !empty($message["release_name"]))
						$name = $message["release_name"];
					if(isset($message["model"]) && isset($message["model"]["cover_half"]) && !empty($message["model"]["cover_half"]))
						$icon = $message["model"]["cover_half"];
				}
			}
			//$responseString = '{"success":false}';
			//echo "<!--" . $name . " " . $icon . "-->";
			//echo "<!--" . $responseString . "-->";
		}
		// 关闭 cURL 会话
		curl_close($ch);
	}
?>

<!DOCTYPE html>

<html class="notranslate" translate="no">

<head>
	<meta charset="utf-8" name="viewport" content="viewport-fit=cover, width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
	<meta name="google" content="notranslate">
	<meta content="光陣三維 - 3D名片" property="og:description">
	<!--<meta content="https://businesscard.lightmatrix3d.com/lightmatrix.png" property="og:image">
	<title>3D名片</title>-->
	<?php
		echo '<meta content="' . $icon . '" property="og:image">';
		echo "\n";
		echo '	<title>' . $name . '</title>';
		echo "\n";
	?>
	<link href="video-js.min.css?decache=3" rel="stylesheet">
	<style>
		@charset "UTF-8";
		* {
			-moz-user-select: none;
			-webkit-user-select: none;
			-ms-user-select: none;
			user-select: none;
			-o-user-select: none;
			-webkit-tap-highlight-color: transparent;
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
			-webkit-overflow-scrolling: touch;
			-webkit-touch-callout: none;
			-webkit-text-size-adjust: none;
		}
		*:focus {
			outline: none;
		}
		html, body {
			margin: 0px;
			padding: 0px;
			width: 100%;
			height: 100%;
			overflow: hidden;
			background-color: #2B2B2B;
			font-family: "Helvetica Neue", Helvetica, Arial, "微軟正黑體", "微软雅黑", "メイリオ", "맑은 고딕", sans-serif;
			-webkit-text-size-adjust: none;
			touch-action: manipulation;
		}
		#player {
			position: fixed;
			width: 100%;
			height: 100%;
			/*z-index: 2;*/
			/*padding-top: 56.25%;*/
		}
		#player .play-video {
			/*width: 100px;
			height: 100px;
			background-color: aqua;*/
			position: absolute;
			left: 50%;
			top: 50%;
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%);
			/*pointer-events: none;*/
			padding: 10px;
		}
		#player .play-video img {
			width: 110px;
			height: 110px;
		}
		#player .play-video > div {
			border-radius: 100%;
			background-color: rgba(0, 0, 0, 0.5);
			/*border: 1px solid #FFFFFF;*/
			font-size: 0px;
		}
		.video-js .vjs-big-play-button {
			display: none;
		}
		.video-js .vjs-control-bar {
			background-color: transparent;
			margin-bottom: max(20px, constant(safe-area-inset-bottom));
			margin-bottom: max(20px, env(safe-area-inset-bottom));
			padding-left: max(20px, constant(safe-area-inset-left));
			padding-left: max(20px, env(safe-area-inset-left));
			padding-right: max(20px, constant(safe-area-inset-right));
			padding-right: max(20px, env(safe-area-inset-right));
		}
		#video {
			position: absolute;
			width: 100%;
			height: 100%;
			top: 0px;
			overflow: hidden;
		}
		#three {
			position: absolute;
			top: 0px;
			width: 100%;
			height: 100%;
			overflow: hidden;
		}
		#log {
			position: fixed;
			top: 20px;
			left: 20px;
			color: #00FF00;
			pointer-events: none;
			z-index: 3;
		}
		#start-page {
			position: fixed;
			top: 0px;
			width: 100%;
			height: 100%;
			background-color: #2B2B2B;
			opacity: 1;
			-webkit-transition: opacity 1s;
			transition: opacity 1s;
			z-index: 2;
		}
		#start-page.finish {
			opacity: 0;
			pointer-events: none;
		}
		#start-page.error #loading {
			display: none;
		}
		#loading {
			/*width: 70%;
			max-width: 300px;*/
			position: absolute;
			/*height: 1px;
			background-color: #444444;*/
			left: 50%;
			top: 50%;
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%);
			width: 110px;
			height: 110px;
			-webkit-animation: spin 1s linear infinite; /* Safari */
			animation: spin 1s linear infinite;
		}
		/*#loading.loading {
			-webkit-animation: spin 1s linear paused;
			animation: spin 1s linear paused;
		}*/
		/*#loading > div {
			width: 0%;
			height: 100%;
			background-color: #CCCCCC;
			overflow: hidden;
			-webkit-transition: width 0.3s;
			transition: width 0.3s;
		}*/
		/* Safari */
		@-webkit-keyframes spin {
		0% { -webkit-transform: translate(-50%, -50%) rotate(0deg); }
		100% { -webkit-transform: translate(-50%, -50%) rotate(360deg); }
		}
		@keyframes spin {
		0% { transform: translate(-50%, -50%) rotate(0deg); }
		100% { transform: translate(-50%, -50%) rotate(360deg); }
		}
		#info {
			padding-left: 20px;
			padding-right: 20px;
			text-align: center;
			width: 100%;
			position: absolute;
			left: 50%;
			top: 50%;
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%);
			color: #FFFFFF;
			font-size: 17px;
		}
		#vignette {
			width: 100%;
			height: 100%;
			position: fixed;
			top: 0px;
			pointer-events: none;
		}
		#button {
			position: fixed;
			right: 0px;
			bottom: 0px;
			/*display: flex;
			flex-direction: row;
			gap: 10px;*/
			max-height: 100%;
			overflow-y: auto;
			padding: 10px;
			padding-right: max(10px, constant(safe-area-inset-right));
			padding-right: max(10px, env(safe-area-inset-right));
			padding-bottom: max(10px, constant(safe-area-inset-bottom));
			padding-bottom: max(10px, env(safe-area-inset-bottom));
		}
		#button div {
			margin: 15px;
			width: 55px;
			height: 55px;
			background-color: rgba(128, 128, 128, 0.7);
			border: 2px solid #FFFFFF;
			position: relative;
			border-radius: 100%;
		}
		#button a {
			display: none;
		}
		body.pc #button a > div {
			-webkit-transition: background-color 0.5s;
			transition: background-color 0.5s;
		}
		#button a:active > div {
			transform: scale(0.94);
		}
		body.pc #button a:hover > div {
			background-color: rgba(220, 220, 220, 0.7);
		}
		#button img {
			position: absolute;
			width: 60%;
			height: 60%;
			left: 50%;
			top: 50%;
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%);
		}
		#logo {
			color: #FFFFFF;
			top: max(20px, env(safe-area-inset-top));
			left: max(20px, env(safe-area-inset-left));
			margin-right: max(20px, env(safe-area-inset-left));
			position: fixed;
			padding-top: 2px;
			padding-bottom: 4px;
			padding-left: 12px;
			padding-right: 12px;
			border: 1px solid #FFFFFF;
			background-color: rgba(0, 0, 0, 0.5);
			/*-ms-transform-origin: 100% 0%;
			-ms-transform: scale(0.8);
			transform-origin: 100% 0%;
			transform: scale(0.8);*/
			cursor: pointer;
		}
		#logo:active {
			opacity: 0.75;
		}
		#logo > div {
			text-align: center;
			padding: 2px;
			font-size: 16px;
		}
		#logo > .english {
			font-size: 10px;
		}
		#powered {
			padding-left: max(25px, env(safe-area-inset-left));
			padding-right: max(25px, env(safe-area-inset-right));
			position: fixed;
			width: 100%;
			bottom: max(10px, env(safe-area-inset-bottom));
			text-align: center;
			pointer-events: none;
		}
		#powered a {
			font-size: 12px;
			color: #FFFFFF;
			text-decoration: none;
			pointer-events: auto;
		}
		#powered a:active {
			opacity: 0.75;
		}
	</style>
	<!--<script src="vconsole.min.js"></script>
	<script>
		var vConsole = new window.VConsole();
	</script>-->
	<script src="jquery-3.4.1.min.js"></script>
	<script src="video.min.js?decache=3"></script>
	<script src="progressbar.min.js"></script>
	<script src="three/three.min.js"></script>
	<script src="three/BufferGeometryUtils.js"></script>
	<script src="three/AjaxTextureLoader.js"></script>
	<script src="three/three.doc.js"></script>
	<script src="three/fflate.min.js"></script>
	<script src="three/FBXLoader.js"></script>
	<script src="three/OBJLoader.js"></script>
	<!--<script src="three/GLTFLoader.js"></script>-->
	<script src="three/SkeletonUtils.js"></script>
	<script src="three/OrbitControls.js"></script>
	<script>
		var urlData = {};
		(function() {
			var urlSite = String(document.URL).split('?');
			if (urlSite[1]) {
				var varGroup = urlSite[1].split('&');
				for (var i in varGroup)
					urlData[String(varGroup[i].split('=')[0]).toLowerCase()] = String(varGroup[i].split('=')[1]).toLowerCase();
			}
		})();

		$(function() {//return;

			//參數
            var isMobile = false;
            (function(a) {
                if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) isMobile = true;
            })(navigator.userAgent || navigator.vendor || window.opera);
            var deviceRatio = 1;
            (function() {
                if(!isMobile) $('body').addClass('pc');
                $('body').append('<canvas></canvas>');
                var ctx = $('canvas')[0].getContext('2d');
                deviceRatio = (window.devicePixelRatio || 1) / (ctx.webkitBackingStorePixelRatio ||
                    ctx.mozBackingStorePixelRatio ||
                    ctx.msBackingStorePixelRatio ||
                    ctx.oBackingStorePixelRatio ||
                    ctx.backingStorePixelRatio || 1);
                $('canvas').remove();
            })();
            var ios = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);
            if(ios) window.createImageBitmap = undefined;
			//info.json要用此網址
			var infoIP = 'https://lm3d.s3.ap-northeast-1.amazonaws.com/businesscard/';
			//var infoIP = 'businesscard/';
			//其他資源用cloudfront的網址，比較快
			var ip = 'https://d21ybuez47qzdu.cloudfront.net/businesscard/';
			//var ip = infoIP;
			var path = '';
			var decache = '';
			var freeview = urlData.mode == '2';
			var initFreeview = null;
			var cardData = {};

            //訊息
            function setMessage(message) {
				if($('#start-page.error')[0]) return;
                $('#info').text(message || '');
				$('#start-page').removeClass('finish').addClass('error');
            }

			//讀取條
			var bar = new ProgressBar.Circle('#loading', {
				strokeWidth: 1,
				duration: 1000,
				easing: 'easeOut',
				color: '#CCCCCC',
				trailColor: '#444444',
				trailWidth: 1,
				svgStyle: null/*,
				step: function(state, circle) {
					circle.setText(Math.floor(circle.value() * 100) + '%');
				}*/
			});
			bar.set(0.25);

			//檢查相容性
			var renderer = null;
			try {
				renderer = new THREE.WebGLRenderer({
					antialias: !isMobile
					//logarithmicDepthBuffer: true,
					//alpha: true,
					//preserveDrawingBuffer: true // required to support .toDataURL()
				});
			} catch (e) {
				setMessage('您的瀏覽器不支援WebGL，請使用其他瀏覽器嘗試');
				return;
			}
			var support8192 = !ios && parseInt(renderer.capabilities.maxTextureSize) > 4096;//是否支援8192

			//讀取名片資訊
			if(urlData.id) {
				<?php
					echo "var response = '" . $responseString . "';";
				?>
				var msg = '';
				if(response)
					try {
						msg = JSON.parse(response);
					} catch (e) {
						msg = '';
					}
				console.log(msg);
				//if(String(urlData.id).length >= 20/*urlData.id == 2*/) //新版
				if(msg)
					if(msg.success && msg.message && msg.message.model) {
						cardData = msg.message;
						cardData.cardFront = cardData.card_front;
						cardData.cardBack = cardData.card_back;
						cardData.texture = cardData.model.texture;
						cardData.mesh = cardData.model.mesh;
						cardData.name = cardData.release_name;
						if(!cardData.cardFront) cardData.hideFrontCard = true;
						if(!cardData.cardBack) cardData.hideBackCard = true;
						initPlayer();
					} else
						setMessage((typeof msg.message == 'string' ? msg.message : '') || '找不到名片資訊');
					/*$.ajax({
						url: 'https://businesscard2.lightmatrix3d.com/api/getBC',
						data: {
							public_id: String(urlData.id)
						},
						timeout: 30000,
						type: 'POST',
						dataType: 'json',
						//contentType : 'application/x-www-form-urlencoded; charset=UTF-8',
						success: function(msg) {
							console.log(msg);
							msg = msg || {};
							msg = msg[0] || msg;
							if(msg.success && msg.message && msg.message.model) {
								cardData = msg.message;
								cardData.cardFront = cardData.card_front;
								cardData.cardBack = cardData.card_back;
								cardData.texture = cardData.model.texture;
								cardData.mesh = cardData.model.mesh;
								if(!cardData.cardFront) cardData.hideFrontCard = true;
								if(!cardData.cardBack) cardData.hideBackCard = true;
								initPlayer();
							} else
								setMessage((typeof msg.message == 'string' ? msg.message : '') || '找不到名片資訊');
						},
						error: function(xhr, ajaxOptions, thrownError) {
							console.warn(xhr, ajaxOptions, thrownError);
							setMessage('找不到名片資訊');
						}
					});*/
				else { //舊版
					function setCardPath(value) {
						path = value;
						cardData.mesh = path + 'mesh.' + (cardData.format ? String(cardData.format).toLowerCase() : 'obj');
						cardData.texture = path + (support8192 ? '8192.jpg' : '4096.jpg');
						if(!cardData.hideFrontCard)
							cardData.cardFront = path + 'front.jpg';
						if(!cardData.hideBackCard)
							cardData.cardBack = path + 'back.jpg';
						//
						initPlayer();
					}
					$.ajax({
						url: infoIP + urlData.id + '/info.json?decache=' + new Date().getTime(),
						dataType: 'text',
						timeout: 10000,
						success: function(data) {
							try {
								data = JSON.parse(data);
							} catch (e) {
								data = {};
							}
							cardData = data;
							if(data.decache)
								decache = '?decache=' + data.decache;//new Date().getTime();
							if(data.s3)
								ip = infoIP;
							setCardPath(ip + (data.id ? data.id : urlData.id) + '/');
						},
						error: function() {
							setCardPath(ip + urlData.id + '/');
						}
					});
				}
			} else
				setMessage('找不到模型');

			//播放器
			function initPlayer() {
				if(freeview) {
					cardData.freeview = cardData.freeview || {};
					var source = {src: path + (cardData.freeview.video || 'video.mp4') + decache};
					videojs.log = function() {};
					/*videojs.log.error = */videojs.log.warn = videojs.log;
					//videojs.Vhs.GOAL_BUFFER_LENGTH = Math.abs(parseInt(urlData.goalbuffer) || 10);
					//videojs.Vhs.MAX_GOAL_BUFFER_LENGTH = Math.abs(parseInt(urlData.buffer) || 30);
					//videojs.Vhs.BACK_BUFFER_LENGTH = Math.abs(parseInt(urlData.backbuffer) || 10);
					var player = videojs('video', {
						liveui: true,
						sources: [source],
						//playbackRates: [0.25, 0.5, 1],
						/*html5: {
							nativeAudioTracks: false,
							nativeVideoTracks: false
						},*/
						muted: true,
						autoplay: true,
						inactivityTimeout: 0,
						textTrackSettings: false,
						controlBar: {
							fullscreenToggle: false,
							pictureInPictureToggle: false,
							subtitlesButton: false,
							//volumePanel: false,
							//volumeControl: false,
							volumePanel: {inline: false}
						}
					}, function() {
						var me = this;
						//播放器設定
						//$('<div id="three"></div><img id="vignette" src="vignette.png"/>').insertAfter('#video video');
						$('#three,#vignette').insertAfter('#video video');
						try {
							this.tech_.off('dblclick');
							/*setTimeout(function() {
								me.play();
							}, 100);*/
						} catch(e) {
							setMessage('無法播放影片');
							return;
						}
						this.on('error', function() {
							setMessage('無法播放影片');
						});
						$('#player').click(function(e) {
							$(this).unbind('click');
							$('#player>.play-video').remove();
							me.play();
							me.muted(false);
						});
						/*requestAnimationFrame(function(){
							player.currentTime(5);
							initThree();
						});*/
						var timer = setInterval(function() {
							//console.log(player.currentTime())
							if(me.currentTime() > 0) {
								clearInterval(timer);
								//$('#player').unbind('click');
								$('#player>.play-video').remove();
								setTimeout(function() {
									if(initFreeview) initFreeview();
									else initFreeview = true;
								}, 300);
							}
						}, 300);
						//player.currentTime(5);
						initThree();
					});
				} else {
					$('#player').remove();
					initThree();
				}
			}

			function initThree() {
				//Three
				/*var renderer = null;
				try {
					renderer = new THREE.WebGLRenderer({
						antialias: !isMobile
						//logarithmicDepthBuffer: true,
						//alpha: true,
						//preserveDrawingBuffer: true // required to support .toDataURL()
					});
				} catch (e) {
					setMessage('您的瀏覽器不支援WebGL，請使用其他瀏覽器嘗試');
					return;
				}*/
				renderer.setClearColor('#000000');
				renderer.setPixelRatio(deviceRatio);//console.warn(deviceRatio);
				renderer.shadowMap.enabled = true;
				//renderer.shadowMap.type = THREE.PCFSoftShadowMap;
				renderer.autoClear = false;
				$('#three')[0].appendChild(renderer.domElement);
				var scene = new THREE.Scene();
				var scene2 = new THREE.Scene();
				//scene.fog = new THREE.FogExp2('#000000', 0.005);
				//scene.fog = new THREE.Fog('#000000', 500, 1200);
				scene.fog = new THREE.Fog('#000000', 0, 1750);
				scene2.fog = new THREE.Fog('#000000', 0, 1750);
				var camera = new THREE.PerspectiveCamera(65, $(three).width() / $(three).height(), 1, 1000000);
				camera.rotation.order = 'YXZ';
				var camera2 = new THREE.PerspectiveCamera(65, $(three).width() / $(three).height(), 1, 1000000);
				//camera.position.set(0, 160, 0);//眼睛離地160cm
				//camera.position.set(400, 200, 0);
				//camera.rotation.set(-90 * (Math.PI / 180), 0, 0);

				scene.add(camera);
				var hemiLight = new THREE.HemisphereLight('#FFFFFF', '#444444', 0.1);
				hemiLight.position.set(0, 200, 0);
				scene.add(hemiLight);
				var directionalLight = new THREE.DirectionalLight('#FFFFFF', 0.5);
				directionalLight.position.set(-0.5, 1, 0.3);
				if(!freeview) {
					directionalLight.castShadow = true;
					directionalLight.shadow.camera.near = -2500;
					directionalLight.shadow.camera.far = 2500;
					directionalLight.shadow.camera.top = 2500;
					directionalLight.shadow.camera.bottom = -2500;
					directionalLight.shadow.camera.left = -2500;
					directionalLight.shadow.camera.right = 2500;
					directionalLight.shadow.mapSize.width = 4096;//support8192 ? 8192 : 4096;
					directionalLight.shadow.mapSize.height = 4096;//support8192 ? 8192 : 4096;
				}
				//modelGroup.add(directionalLight);
				scene.add(directionalLight);
				//
				var spotLight = new THREE.SpotLight('#FFFFFF', 12); //7
				spotLight.position.set(0, 500, 0);
				spotLight.distance = 600;
				spotLight.angle = 0.4;
				spotLight.decay = 1;
				spotLight.penumbra = 1;
				scene2.add(spotLight);

				var updateFreeview = null;
				var box = new THREE.Box3();

				//loading
				var loadSourceList = [];
				function loadSource(name, callback, optional) {
					loadSourceList.push({
						name: String(name),
						callback: callback,
						loaded: 0,
						total: 0,
						optional: optional,
						complete: false
					});
				}
				function startLoading() {
					//var date = new Date();
					//decache = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
					var fbxLoader = null;//new THREE.FBXLoader();
					var objLoader = null;//new THREE.OBJLoader();
					//var gltfLoader = null;
					var textureLoader = new THREE.AjaxTextureLoader();//AjaxTextureLoader ImageLoader
					var loadedCount = 0;
					function complete(index) {
						if(loadSourceList[index].complete) return;//不要完成2次(應該不會發生)
						loadSourceList[index].complete = true;
						loadedCount++;
						if(loadedCount >= loadSourceList.length)
							requestAnimationFrame(function() {
								loadingCompleted();
							});
					}
					function progress(index, xhr) {
						loadSourceList[index].loaded = Math.min(xhr.loaded || 0, xhr.total || 0);
						loadSourceList[index].total = xhr.total || 0;
						var loaded = 0;
						loadSourceList.forEach(function(e) {
							if(!e.error) loaded += e.loaded;
						});
						var allTotalReady = true;
						var total = 0;
						loadSourceList.forEach(function(e) {
							if(!e.error) {
								if(e.total <= 0) allTotalReady = false;
								total += e.total;
							}
						});
						if(allTotalReady)
							bar.animate(Math.min(1, loaded / total));
							//$('#loading>div').css('width', (Math.min(1, loaded / total) * 100) + '%');
					}
					function error(index) {
						loadSourceList[index].error = true;
						complete(index);
					}
					function load(index, name, callback, optional) {
						//console.log(index, name, optional)
						var type = name.split('.');
						type = type[type.length - 1];
						type = type.split('?')[0];
						switch (type/*[type.length - 1]*/.toLowerCase()) {
							case 'obj':
								if(!objLoader) objLoader = new THREE.OBJLoader();
								objLoader.load(name + decache, function(object) {
									if(typeof callback == 'function') callback(object);
									complete(index);
								}, function(xhr) {
									progress(index, xhr);
								}, function(e) {
									if(optional) error(index);
									else setMessage('無法讀取模型');
								});
								break;
							/*case 'glb':
								if(!gltfLoader) gltfLoader = new THREE.GLTFLoader();
								gltfLoader.load(name, function(object) {
									if(typeof callback == 'function') callback(object);
									complete();
								}, function(xhr) {
									progress(index, xhr);
								}, function(e) {
									setMessage('無法讀取模型');
								});
								break;*/
							case 'fbx':
								if(!fbxLoader) fbxLoader = new THREE.FBXLoader();
								fbxLoader.load(name, function(object) {
									/*object.mixer = new THREE.AnimationMixer(object);
									var action = object.mixer.clipAction(object.animations[0]);
									action.play();*/
									if(object.animations[0]) {
										mixer = new THREE.AnimationMixer(object);
										mixer.clipAction(object.animations[0]).play();
									}
									if(typeof callback == 'function') callback(object);
									complete(index);
								}, function(xhr) {
									progress(index, xhr);
								}, function(e) {
									if(optional) error(index);
									else setMessage('無法讀取模型');
								});
								break;
							default:
								/*var textureLoader = new THREE.TextureLoader().load(name + '?decache=' + decache, function(object) {
									if(typeof callback == 'function') callback(textureLoader);
									complete();
								}, function(xhr) {
									progress(index, xhr);
								}, function(e) {
									setMessage('無法讀取貼圖');
								});*/
								textureLoader.load(name + decache, function(object) {
									if(typeof callback == 'function') callback(object);
									complete(index);
								}, function(xhr) {
									progress(index, xhr);
								}, function(e) {
									if(optional) error(index);
									else setMessage('無法讀取貼圖');
								});
						}
					}
					loadSourceList.forEach(function(e, i) {
						load(i, e.name, e.callback, e.optional);
					});
				}
				var modelTexture = null;
				var floorTexture = null;
				var equirecTexture = null;
				var cardFrontTexture = null;
				var cardBackTexture = null;
				var model = null;
				var mixer = null;
				var modelReflection = null;
				var reflectionMixer = null;
				var freeviewTexture = null;//自由視角的第一個影格
				var setting = {
					r: 0,
					g: 255,
					b: 0,
					similarity: 0.4,
					smoothness: 0.3,
					edge: 0.8,
					height: 180,
					bottom: 18,
					//shadowBottom: 15,
					column: 4,
					row: 3,
					range: 120
				};
				loadSource(ip + 'floor.png', function(object) {
					object.wrapS = object.wrapT = THREE.RepeatWrapping;
					//object.repeat.x = object.repeat.y = 10;
					object.repeat.set(250, 250);
					floorTexture = object;
				});
				//if(!unlit)
				loadSource(ip + 'environment.jpg', function(object) {
					object.mapping = THREE.EquirectangularReflectionMapping;
					object.encoding = THREE.sRGBEncoding;
					equirecTexture = object;
				});
				//document.title = urlData.id;
				if(freeview)
					loadSource(path + (cardData.freeview.preview || 'preview.jpg'), function(object) {
						//object.minFilter = object.magFilter = THREE.LinearFilter;
						//object.wrapS = object.wrapT = THREE.RepeatWrapping;
						freeviewTexture = object;
					}, true);
				else
					loadSource(cardData.texture/*path + (support8192 ? '8192.jpg' : '4096.jpg')*/, function(object) {
						modelTexture = object;
					});
				//document.title = cardData.name || urlData.id;
                if(cardData.name) document.title = cardData.name;
				if(!freeview && cardData.sound) {
					$('#sound').show();
					$('#music source').attr('src', path + 'sound.mp3' + decache);
				}
				if(cardData.email)
					$('#email').attr('href', 'mailto:' + cardData.email).show();
				if(cardData.phone)
					$('#phone').attr('href', 'tel:' + cardData.phone).show();
				if(cardData.telegram)
					$('#telegram').attr('href', 'https://telegram.me/' + cardData.telegram).show();
				if(cardData.whatsapp)
					$('#whatsapp').attr('href', 'https://wa.me/' + cardData.whatsapp).show();
				if(cardData.facebook)
					$('#facebook').attr('href', cardData.facebook).show();
				if(cardData.instagram)
					$('#instagram').attr('href', cardData.instagram).show();
				if(cardData.twitter)
					$('#twitter').attr('href', cardData.twitter).show();
				if(cardData.web)
					$('#web').attr('href', cardData.web).show();
				if(cardData.cardFront)
					loadSource(cardData.cardFront/*path + 'front.jpg'*/, function(object) {
						cardFrontTexture = object;
					}, true);
				if(cardData.cardBack)
					loadSource(cardData.cardBack/*path + 'back.jpg'*/, function(object) {
						cardBackTexture = object;
					}, true);
				if(freeview)
					for(var i in cardData.freeview)
						setting[i] = cardData.freeview[i];
				else
					loadSource(cardData.mesh/*path + 'mesh.' + (cardData.format ? String(cardData.format).toLowerCase() : 'obj')*/, function(object) {
						model = object;
					});
				if(freeview)
					$('#button').css({
						bottom: (parseFloat($('.video-js .vjs-control-bar').css('margin-bottom')) + $('.video-js .vjs-control-bar').outerHeight()) + 'px',
						'padding-bottom': '0px'
					});
				startLoading();
				//客製化
				switch(cardData.organization) {
					case 'NationalFireAgency':
						$('#logo').parent('a').attr('href', 'https://www.nfa.gov.tw/');
						$('#logo > div').not('.english').html('內政部<br/>消防署');
						$('#logo .english').remove();//text('National Fire Agency');
						break;
					case 'LightMatrix':
						$('#powered').remove();
						break;
					default:
						$('#logo').remove();
						break;
				}
				/*$.ajax({
					url: path + 'info.json?decache=' + decache,
					dataType: 'text',
					timeout: 10000,
					success: function(data) {
						try {
							data = JSON.parse(data);
						} catch (e) {
							data = {};
						}
						load(data);
					},
					error: function() { 
						load({});
					}
				});*/

				//事件
				var clock = new THREE.Clock();
				var controls = new THREE.OrbitControls(camera, renderer.domElement);
				controls.enableDamping = true; // an animation loop is required when either damping or auto-rotation are enabled
				controls.dampingFactor = 0.15;
				controls.screenSpacePanning = true;
				controls.enablePan = !freeview;
				controls.minDistance = freeview ? 120 : 85;
				controls.maxDistance = 400;
				//controls.defaultDistance = 280;
				controls.rotateSpeed = 1.3;
				//controls.panSpeed = 0.5;
				controls.autoRotate = true;
				//controls.enabled = false;
				//controls.autoRotateSpeed = 0.7;
				//controls.enablePan = false;
				//camera.position.set(0, 150, 170);
				//controls.target.set(0, 165 / 2, 0);
				//controls.update();
				var dragPosition = null;
				controls.onStartDrag = function(e) {
					if ((!this.autoRotate || $('#player>.play-video')[0]) && (!e.touches || e.touches.length == 1))
						dragPosition = {
							x: (e.touches ? e.touches[0] : e).screenX,
							y: (e.touches ? e.touches[0] : e).screenY
						};
					this.autoRotate = false; 
				};
				controls.onDrag = function(e) {
					if (e.touches && e.touches.length > 1) return;
					if (dragPosition) {
						var deltaX = (e.touches ? e.touches[0] : e).screenX - dragPosition.x;
						var deltaY = (e.touches ? e.touches[0] : e).screenY - dragPosition.y;
						if (deltaX * deltaX + deltaY * deltaY > 4)
							dragPosition = null;
					}
				};
				controls.onStopDrag = function(e) {
					if (e.touches && e.touches.length > 0) return;
					if (dragPosition) {
						this.autoRotate = true;
						dragPosition = null;
					}
				};
				//3D投影到2D
				function project(vector) {
					var width = $(three).width();
					var height = $(three).height();
					var widthHalf = width / 2;
					var heightHalf = height / 2;
					var pos = vector.clone();
					pos.project(camera);
					pos.x = (pos.x * widthHalf) + widthHalf;
					pos.y = -(pos.y * heightHalf) + heightHalf;
					return pos;
				}

				function loadingCompleted() {
					//是否是自由視角模式
					if(freeview) {
						/*$('video source').on('error', function (e) {
							setMessage('無法播放影片');
						});
						$('video source').attr('src', path + 'video.mp4?decache=' + decache);
						$('video')[0].load();
						$('video')[0].play();*/
						var vertexShader = [
							'varying vec2 vUv;',
							'void main( void ) {',
								'vUv = uv;',
								'gl_Position = projectionMatrix * modelViewMatrix * vec4(position,1.0);',
							'}'
						].join('');
						var fragmentShader = [
							'uniform vec3 keyColor;',
							'uniform float similarity;',
							'uniform float smoothness;',
							'uniform float edge;',
							'varying vec2 vUv;',
							'uniform sampler2D map;',
							'uniform vec2 scale;',
							'uniform vec2 offset;',
							'uniform float shadow;',
							'void main() {',
								//vec4 videoColor = texture2D(map, vUv/* * 0.333333 + 0.333333*/);
								//float scale = 1.0 / 3.0;
								//vec4 videoColor = texture2D(map, vUv * mat2(scale, 0.0, 0.0, scale) + );
								'vec4 videoColor = texture2D(map, vUv * vec2(scale.x, scale.y) + vec2(offset.x, offset.y));',
						
								'float Y1 = 0.299 * keyColor.r + 0.587 * keyColor.g + 0.114 * keyColor.b;',
								'float Cr1 = keyColor.r - Y1;',
								'float Cb1 = keyColor.b - Y1;',
								
								'float Y2 = 0.299 * videoColor.r + 0.587 * videoColor.g + 0.114 * videoColor.b;',
								'float Cr2 = videoColor.r - Y2;',
								'float Cb2 = videoColor.b - Y2;',
								'float blend = smoothstep(similarity, similarity + smoothness, distance(vec2(Cr2, Cb2), vec2(Cr1, Cb1)));',
								
								//'if (blend < 0.5) discard;',
								'if(distance(vec2(Cr2, Cb2), vec2(Cr1, Cb1)) < edge)',
									'videoColor.g = (videoColor.r + videoColor.b) * 0.5;',

								'if(shadow > 0.0) videoColor.a *= (1.0 - vUv.y) * shadow;',

								'gl_FragColor = vec4(videoColor.rgb, videoColor.a * blend);',
							'}'
						].join('');
						var videoTexture = new THREE.VideoTexture($('video')[0]);
						videoTexture.minFilter = videoTexture.magFilter = THREE.LinearFilter;
						var column = setting.column;
						var row = setting.row;
						function createMaterial() {
							return new THREE.ShaderMaterial({
								transparent: true,
								//depthPacking: THREE.RGBADepthPacking,
								alphaTest: 0.5,
								side: THREE.DoubleSide,
								//depthWrite: false,
								depthTest: false,//urlData.debug == 'true',//false,//////////////////////////
								uniforms: {
									//color: {value: '#FFCC00'},
									map: { value: initFreeview ? videoTexture : (freeviewTexture || videoTexture) },
									//alphaMap: { value: videoTexture },
									//keyColor: { value: [0.0, 1.0, 0.0] },
									keyColor: { value: [setting.r / 255, setting.g / 255, setting.b / 255] },
									//keyColor: { value: [90/255, 198/255, 146/255] },
									similarity: { value: setting.similarity },
									smoothness: { value: setting.smoothness },
									edge: { value: setting.edge },
									scale: { value: [1 / column, 1 / row] },
									offset: { value: [0, 0] },
									shadow: { value: 0 }
								},
								//vertexShader: vertexShader(),
								//fragmentShader: fragmentShader()
								vertexShader: vertexShader,
								fragmentShader: fragmentShader
							});
						}
						var chromakeyMaterial = createMaterial();
						var videoPlane = new THREE.Mesh(
							new THREE.PlaneBufferGeometry(setting.height / 3 * 4, setting.height),
							chromakeyMaterial
							//new THREE.MeshBasicMaterial({map: videoTexture, color: '#FFFFFF', side: THREE.DoubleSide})
							//MeshPhongMaterial MeshBasicMaterial
						);
						videoPlane.position.set(0, setting.height / 2 - setting.bottom, 0);
						scene.add(videoPlane);
						//
						var chromakeyShadowMaterial = createMaterial();//chromakeyMaterial.clone();
						chromakeyShadowMaterial.uniforms.shadow.value = 0.7;
						var videoShadowPlane = new THREE.Mesh(
							new THREE.PlaneBufferGeometry(setting.height / 3 * 4, -setting.height),
							chromakeyShadowMaterial
						);
						videoShadowPlane.position.set(0, -setting.height / 2 - setting.bottom + setting.bottom  * 0.65, 0);
						scene2.add(videoShadowPlane);
						function setVideoIndex(index) {
							index = Math.max(1, Math.min(column * row, parseInt(index) || 1));
							//index = column * row - index + 1;
							chromakeyShadowMaterial.uniforms.offset.value = chromakeyMaterial.uniforms.offset.value = [1 / column * ((index - 1) % column), 1 / row * (row - 1 - Math.floor((index - 1) / column))];
						}
						updateFreeview = function() {
							var x = camera.position.x - videoPlane.position.x;
							var z = camera.position.z - videoPlane.position.z;
							var angle = Math.atan2(x, z) * (180 / Math.PI);//左-右+
							var gapAngle = setting.range / (column * row - 1);
							var startAngle = angle + (column * row / 2) * gapAngle;
							//log(Math.floor(startAngle / gapAngle)+1, true);
							setVideoIndex(Math.floor(startAngle / gapAngle) + 1);
							videoPlane.rotation.set(0, Math.atan2(x, z), /*(parseFloat(sourceData.rotation) || 0) * (Math.PI / 180)*/0);
							videoShadowPlane.rotation.copy(videoPlane.rotation.clone());
						};
						initFreeview = function() {
							chromakeyShadowMaterial.uniforms.map.value = chromakeyMaterial.uniforms.map.value = videoTexture;
						};
						//
						camera.position.set(200, videoPlane.position.y / 2 + 67.5, 0);//自由視角模式重右邊開始轉比較好
						controls.target.set(0, videoPlane.position.y * 1.05, 0);
						controls.minAzimuthAngle = (-setting.range / 2 - 10) * (Math.PI / 180);
						controls.maxAzimuthAngle = (setting.range / 2 + 10) * (Math.PI / 180);
						controls.minPolarAngle = (90/* - 30*/) * (Math.PI / 180);
						controls.maxPolarAngle = (90/* + 15*/) * (Math.PI / 180);
					} else {
						$('video').remove();
						camera.position.set(0, 150, 170);
						controls.target.set(0, 165 / 2, 0);
						//
						var shadowPlane = new THREE.Mesh(new THREE.PlaneGeometry(10000, 10000), new THREE.ShadowMaterial({opacity: 0.3, depthWrite: false}));
						shadowPlane.rotateX(-Math.PI / 2);
						shadowPlane.receiveShadow = true;
						scene.add(shadowPlane);
						//
						var clippingPlanes = [new THREE.Plane(new THREE.Vector3(0, 1, 0))];
						//modelClippingPlane.constant = -20;
						model.traverse(function(node) {
							if(node.isMesh) {
								node.castShadow = true;
								node.receiveShadow = true;
								//if(node.geometry) node.geometry.computeBoundingBox();
								//console.log(node.geometry)
								node.geometry.boundingSphere = new THREE.Sphere(new THREE.Vector3(0, 0, 0), 100000000);
								//node.geometry.boundingBox = new THREE.Box3(new THREE.Vector3(1000, 1000, 1000),new THREE.Vector3(-1000, -1000, -1000));
							}
							if (node.material)
								node.material = new THREE.MeshBasicMaterial({
									generateMipmaps: false,
									map: modelTexture,
									clippingPlanes: clippingPlanes
								});
							/*if (!unlit && node.geometry) {
								//強制設平滑組
								node.geometry.deleteAttribute('normal');
								var geometry = new THREE.BufferGeometryUtils.mergeVertices(node.geometry);
								geometry.computeVertexNormals();
								node.geometry = geometry;
							}*/
						});
						//計算尺寸
						scene.add(model);
						box.setFromObject(model);
						var width = box.max.x - box.min.x;
						var height = box.max.y - box.min.y;
						var length = box.max.z - box.min.z;
						var scale = 165 / Math.max(Math.max(width, height), length);
						model.scale.set(scale, scale, scale);
						controls.target.set(0, height * scale / 2, 0);
						//renderer.localClippingEnabled = true//box.min.y < 0;
						//倒影
						/***modelReflection = model.clone();
						scene2.add(modelReflection);***/
						modelReflection = THREE.SkeletonUtils.clone(model);
						if(model.animations[0]) {
							reflectionMixer = new THREE.AnimationMixer(modelReflection);
							reflectionMixer.clipAction(model.animations[0]).play();
						}
						var modelReflectionGroup = new THREE.Group();
						modelReflectionGroup.scale.set(scale, scale, scale);
						scene2.add(modelReflectionGroup);
						modelReflectionGroup.add(modelReflection);//
						modelReflection.scale.set(1, 1, 1);//防止大影議員
						var mS = (new THREE.Matrix4()).identity();
						mS.elements[5] = -1;
						///////////modelReflection.applyMatrix4(mS);
						modelReflectionGroup.applyMatrix4(mS);
						modelReflection.traverse(function(node) {
							if (node.material) {
								node.material = new THREE.MeshBasicMaterial({
									generateMipmaps: false,
									map: modelTexture,
									color: '#EEEEEE',
									vertexColors: THREE.VertexColors,
									clippingPlanes: [new THREE.Plane(new THREE.Vector3(0, -1, 0))]
								});
								//node.material.shading = THREE.SmoothShading;
							}
							if (node.geometry) {
								//倒影
								var colorList = [];
								for(var i = 0; i < node.geometry.attributes.position.count; i++) {
									var color = node.geometry.attributes.position.array[i * 3 + 1] / height;
									color = 1 - Math.min(1, Math.max(0, color));
									//color = color * color * color;
									color = Math.min(color, 0.5);
									colorList.push(255 * color);
									colorList.push(255 * color);
									colorList.push(255 * color);
									//if(Math.random()>0.9) console.warn(color);
									/*colorList.push(200 * Math.random() + 55);
									colorList.push(200 * Math.random() + 55);
									colorList.push(200 * Math.random() + 55);*/
								}
								node.geometry.setAttribute('color', new THREE.BufferAttribute(new Uint8Array(colorList), 3, true));
								//node.geometry.colorsNeedUpdate = true;
							}
						});
					}

					//地板
					var floor = new THREE.Mesh(new THREE.PlaneGeometry(5000, 5000), new THREE.MeshPhongMaterial({
						map: floorTexture,
						transparent: true,
						opacity: 0.8,
						shininess: 35
						//color: '#00FF00'
					}));
					floor.rotation.x = -Math.PI / 2;
					floor.rotation.z = Math.PI / 4;
					floor.renderOrder = 1;
					scene2.add(floor);
					//名片
					//上
					if(cardFrontTexture) {
						width = cardFrontTexture.image.width || 90;
						height = cardFrontTexture.image.height || 54;
						scale = 75 / Math.min(width, height);
						var cardFront = new THREE.Mesh(new THREE.PlaneGeometry(width * scale, height * scale), new THREE.MeshStandardMaterial({
							generateMipmaps: false,
							map: cardFrontTexture,
							emissiveMap: cardFrontTexture,
							emissiveIntensity: 0.5,
							emissive: '#FFFFFF',
							//color: '#CCCCCC',
							aoMap: cardFrontTexture,
							roughnessMap: cardFrontTexture,
							metalnessMap: cardFrontTexture,
							//color: '#FFFFFF',
							roughness: 0.5,
							metalness: 0.5,
							envMap: equirecTexture,
							//vertexColors: THREE.VertexColors,
							envMapIntensity: 2
						}));
						cardFront.position.set(0, 100, -50);
						//cardFront.receiveShadow = true;
						//cardFront.visible = urlData.id != '20220908';
						scene.add(cardFront);//scene2
					}
					//下
					if(cardBackTexture) {
						width = cardBackTexture.image.width || 90;
						height = cardBackTexture.image.height || 54;
						scale = 75 / Math.min(width, height);
						var cardBack = new THREE.Mesh(new THREE.PlaneGeometry(width * scale, height * scale), new THREE.MeshStandardMaterial({
							generateMipmaps: false,
							map: cardBackTexture,
							emissiveMap: cardBackTexture,
							emissiveIntensity: 0.5,
							emissive: '#FFFFFF',
							//color: '#CCCCCC',
							aoMap: cardBackTexture,
							roughnessMap: cardBackTexture,
							metalnessMap: cardBackTexture,
							//color: '#FFFFFF',
							roughness: 0.5,
							metalness: 0.5,
							envMap: equirecTexture,
							//vertexColors: THREE.VertexColors,
							envMapIntensity: 2,
							depthWrite: false
						}));
						cardBack.rotation.x = -Math.PI / 2;
						scene.add(cardBack);
					}
					//
					resize();
					update();
					requestAnimationFrame(function() {
						if($('#start-page.error')[0]) return;
						$('#start-page').addClass('finish');
						setTimeout(function() {
							$('#loading').remove();
							//$('#start-page').remove();
						}, 2000);
					});
				}
				
				//resize
				function resize() {
					var w = $(three).width();
					var h = $(three).height();
					//
					camera.aspect = camera2.aspect = w / h;
					camera.updateProjectionMatrix();
					camera2.updateProjectionMatrix();
					//
					/*camera2D.left = -w / 2;
					camera2D.right = w / 2;
					camera2D.top = h / 2;
					camera2D.bottom = -h / 2;
					camera2D.updateProjectionMatrix();*/
					//camera2D.position.z = 10;
					//
					renderer.setSize(w, h);
					render();
				}
				window.addEventListener('resize', resize, false);
				window.addEventListener('orientationchange', function() {
					if (!!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform))
						setTimeout(function() {
							resize();
						}, 500);
					else resize();
				}, false);
				resize();

				//update
				function update() {
					//t = t || 0;
					controls.update();
					camera2.position.copy(camera.position.clone());
					camera2.rotation.copy(camera.rotation.clone());
					//sceneGroup.rotation.set(0, -camera.rotation.y, 0);
					//sceneGroup2.rotation.set(0, -camera.rotation.y, 0);
					//if(videoPlane) videoPlane.rotation.set(0, camera.rotation.y, 0);
					if(updateFreeview) updateFreeview();
					//
					if (mixer || reflectionMixer) {
						var deltaTime = clock.getDelta();
						if(mixer) mixer.update(deltaTime);
						if(reflectionMixer) reflectionMixer.update(deltaTime);
					}
					//
					render();
					requestAnimationFrame(update);
				}
				//render
				function render() {
					if(!freeview) {
						//renderer.localClippingEnabled = !modelReflection && box.min.y < 0;
						if (modelReflection) {
							var p1 = project(new THREE.Vector3(-10, 0, -10));
							var p2 = project(new THREE.Vector3(10, 0, -10));
							var p3 = project(new THREE.Vector3(0, 0, 10));
							//console.warn(p1.x*p2.y+p2.x*p3.y+p3.x*p1.y-p1.y*p2.x-p2.y*p3.x-p3.y*p1.x);
							modelReflection.visible = p1.x * p2.y + p2.x * p3.y + p3.x * p1.y - p1.y * p2.x - p2.y * p3.x - p3.y * p1.x > 0;
							//modelClippingPlane.constant = modelReflection.visible ? -group.position.y : -group.position.y * 2;
							//renderer.localClippingEnabled = false;
						}
						renderer.localClippingEnabled = (!modelReflection || (modelReflection && modelReflection.visible)) && box.min.y < 0;
					}
					//renderer.clear();
					renderer.render(scene2, camera2);
					renderer.clearDepth();
					renderer.render(scene, camera);
				}
			}

        });

        function playSound() {
            $('#music')[0].load();
            $('#music')[0].play();
        }
    </script>
</head>

<body ontouchstart>
	<div id="player">
        <video-js id="video" class="vjs-default-skin vjs-big-play-centered" controls loop playsinline muted preload="auto" crossorigin="anonymous"></video-js>
		<div class="play-video"><div><img src="play.svg"></div></div>
	</div>
    <div id="three"></div>
	<img id="vignette" src="vignette.png"/>
    <div id="powered"><a href="https://www.lightmatrix3d.com/" target="_blank">Powered by Light Matrix Inc. 光陣三維</a></div>
    <div id="button">
        <audio id="music">
            <source type="audio/mpeg">
        </audio>
        <a id="sound" href="javascript:playSound();">
            <div><img src="icon/volume_up.png"></div>
        </a>
        <a id="email" target="_blank">
            <div><img src="icon/email.png"></div>
        </a>
        <a id="phone" target="_blank">
            <div><img src="icon/call.png"></div>
        </a>
        <a id="telegram" target="_blank">
            <div><img src="icon/telegram.png"></div>
        </a>
        <a id="whatsapp" target="_blank">
            <div><img src="icon/whatsapp.png"></div>
        </a>
        <a id="facebook" target="_blank">
            <div><img src="icon/facebook.png"></div>
        </a>
        <a id="instagram" target="_blank">
            <div><img src="icon/instagram.png"></div>
        </a>
        <a id="twitter" target="_blank">
            <div><img src="icon/twitter.png"></div>
        </a>
        <a id="web" target="_blank">
            <div><img src="icon/web.png"></div>
        </a>
    </div>
    <a href="https://www.lightmatrix3d.com/" target="_blank">
        <div id="logo">
            <div>光陣三維</div>
            <div class="english">LightMatrix</div>
        </div>
    </a>
    <div id="start-page">
        <div id="loading"></div>
        <div id="info"></div>
    </div>
    <div id="log"></div>
</body>

</html>