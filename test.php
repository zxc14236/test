<?php
$bssid = $_GET["bssid"];
$ssid = $_GET["ssid"];

if (isset($bssid) && isset($ssid)) {
    //update salt
    $ret = request($bssid, $ssid);
    $ret = json_decode($ret);
    if ($ret->retCd == 0) {
        if (isset($ret->aps[0]->pwd)) {
			$pwd = decryptStrin($ret->aps[0]->pwd);
			$pwd_len = strlen($pwd);
			echo "warnp.style.color='green';";
			echo "warnp.innerHTML='密码长度:$pwd_len,密码:$pwd';";
		//	echo 'test12.innerHTML=jstext;';
		//	echo 'bssid: ' .$bssid. "<br>";
		//	echo 'ssid: ' .$ssid. "<br>";
		//	echo 'PWD: ' .decryptStrin($ret->aps[0]->pwd). "<br>";
        } else {
			echo "warnp.style.color='red';";
			echo "warnp.innerHTML='没有发现密码！';";
		//	echo "document.write('".$bssid."');n"
		//	echo 'msg: sorry! not found';
        //    echo $ret->qryapwd->retMsg;
        }
    }
}

function request($bssid, $ssid)
{
	$data = array();
	$data['origChanId'] = 'xiaomi';
    $data['appId'] = 'A0008';
    $data['ts'] = '1459936625905';
    $data['netModel'] = 'w';
    $data['chanId'] = 'tencent';
    $data['imei'] = '357541051318147';
    $data['qid'] = '';
    $data['mac'] = 'e8:92:a4:9b:16:42';
    $data['capSsid'] = 'test';
    $data['lang'] = 'cn';
    $data['longi'] = '112.888549';
    $data['nbaps'] = '';
    $data['capBssid'] = 'b0:d5:9d:45:b9:85';
    $data['bssid'] = $bssid;
    $data['mapSP'] = 't';
    $data['userToken'] = '';
    $data['verName'] = '4.1.20';
    $data['ssid'] = $ssid;
    $data['verCode'] = '3050';
    $data['uhid'] = 'a0000000000000000000000000000001';
    $data['lati'] = '30.579577';
    $data['dhid'] = 'ff8080814cc5798a014ccbbdfa375369';

/*	$data['origChanId'] = 'juwanaolang';
    $data['appId'] = 'A0008';
    $data['ts'] = '1509170628665';
    $data['netModel'] = 'w';
    $data['chanId'] = 'tencent';
    $data['imei'] = '863925027220296';
    $data['qid'] = '';
    $data['mac'] = '68:df:dd:1b:4b:e7';
    $data['capSsid'] = '2.4G_LINK';
    $data['lang'] = 'cn';
    $data['longi'] = '112.888549';
    $data['nbaps'] = '';
    $data['capBssid'] = '76:7d:24:63:07:f4';
    $data['bssid'] = 'f0:b4:29:df:bc:48';
    $data['mapSP'] = 't';
    $data['userToken'] = '';
    $data['verName'] = '4.1.20';
    $data['ssid'] = 'Xiaomi_BC47';
    $data['verCode'] = '3050';
    $data['uhid'] = 'a0000000000000000000000000000001';
    $data['lati'] = '23.167228';
    $data['dhid'] = 'a83257d07d084dfbbc5bd1da25b74824';*/

	$s2=urlencode(json_encode($data));
//	echo 'json_encode: ' .$s2. "<br>";
//	echo 'strlen: ' .(strlen($s2) % 16). "<br>";

	$slen = strlen($s2);
	$i = 0;
	while($i < 16 - $slen % 16) {
		$s2 = $s2 . " ";
		$i++;
	} 

	$keys = '!I50#LSSciCx&q6E';
	$iv='$t%s%12#2b474pXF';
	$cipher_alg = MCRYPT_RIJNDAEL_128;
	$decrypted_string = mcrypt_encrypt($cipher_alg, $keys, $s2, MCRYPT_MODE_CBC, $iv);
	$ed = strtoupper(bin2hex($decrypted_string));

//	echo 'ed: ' .$ed. "<br>";
	
	$salt = '*Lm%qiOHVEedH3%A^uFFsZvFH9T8QAZe';
    $request_str = '';
	$dt = array();
	$dt['appId'] = 'A0008';
    $dt['pid'] = '00300109';
    $dt['ed'] = $ed;
    $dt['st'] = 'm';
    $dt['et'] = 'a';
    // 对应apk中的 Arrays.sort 数组排序，测试PHP需用 ksort
    ksort($dt);
    foreach ($dt as $key => $value) {
        $request_str .= $value;
    }
    $sign = md5($request_str . $salt);
	$dt['sign'] = $sign;
//	echo 'ed: ' .$sign. "<br>";

	$curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'http://ap.51y5.net/ap/fa.sec');
   // curl_setopt($curl, CURLOPT_USERAGENT, 'WiFiMasterKey/1.1.0 (Mac OS X Version 10.10.3 (Build 14D136))');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // stop verifying certificate
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    // enable posting
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($dt));
    // post imageso
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    // if any redirection after upload
    $contain = curl_exec($curl);
    curl_close($curl);
//	echo 'ed: ' .$contain. "<br>";

//	$pwd = decryptStrin($contain);

	return $contain;
//	$ret = json_decode($contain);
//	$pwd = decryptStrin($ret->aps[0]->pwd);
//	echo 'retCd: ' .$pwd. "<br>";
}

function decryptStrin($str, $keys = '!I50#LSSciCx&q6E', $iv = '$t%s%12#2b474pXF', $cipher_alg = MCRYPT_RIJNDAEL_128)
{
    //Wi-Fi万能钥匙密码采用 AES/CBC/NoPadding 方式加密
    //[length][password][timestamp]
    $decrypted_string = mcrypt_decrypt($cipher_alg, $keys, pack("H*", $str), MCRYPT_MODE_CBC, $iv);
    return substr(trim($decrypted_string), 3, -13);
}

?>
