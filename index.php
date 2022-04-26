<?php
require_once ('libs/autoloader.php');
require_once ('init.php');

$key_bytes = array(0x54, 0x68, 0x61, 0x74, 0x73, 0x20, 0x6D, 0x79, 0x20, 0x4B, 0x75, 0x6E, 0x67, 0x20, 0x46, 0x75, 0x54, 0x68, 0x61, 0x74, 0x73, 0x20, 0x6D, 0x79, 0x20, 0x4B, 0x75, 0x6E, 0x67, 0x20, 0x46, 0x75);

$aeskey = implode(array_map("chr", $key_bytes));
$global -> aeskey = $aeskey;



$funcs -> Agent_Check("taisys_card");



main();





// 3011120170000002/s/token/program  --> server

// 0x6C,0x00  <-- 心跳
// 0x6C,0XFC  <-- 有指令  token OS serial_NUM APDU
// 0x73,0xFB  <-- 通道關閉 
// 0x6C,0xFB  <-- 關閉，馬上重開長連接

// 3011120170000002/s/token/program/serial Num/response data  --> server


function main(){
  global $global;

  if(!empty($global -> input) && strlen($global -> input) > 32){
    // 解密

    $deb64 = base64_decode($global -> input);
    $source = $global -> aes -> decrypt($global -> aeskey, $deb64);
    $input_source = explode("/", $source);

    
    
    //$input_source["token"] = sprintf("%02X", ord($input_source[2][0])).sprintf("%02X", ord($input_source[2][1]));
    $input_source["token"] = hexdec(sprintf("%02X", ord($input_source[2][0])).sprintf("%02X", ord($input_source[2][1])));
    $input_source["program"] = sprintf("%X", ord($input_source[3][0]));

    
  
    if(!empty($input_source)){
      // 卡號為空
      if(empty($input_source[0])){
        exit;
      }

      if(!redis_connect($global -> redis, $global -> conf['REDIS_SERVER'], $global -> conf['REDIS_PASSWD'])){
        exit;
      }

      
      
      $input_source["program"] = $global -> map -> program_mapping($input_source["program"]);
      $input_source["index"] = $input_source[0]."_".$input_source["program"];
      $queue_info = $global -> redis -> get($input_source["index"]."_info");
      $info = array();

      if(!empty($queue_info)){
        $info = explode(",", $queue_info);
      }

      $input_source["os"] = $info[0] != "ios" ? 0 : 1; // 0 = ios, 1 = android
      $input_source["conn_type"] = $conn_type = $info[1] == 1 ? 0x6C : 0x73; // 0x73 短連接, 0x6C 長連接 

      

      if($input_source[1] == "r"){
        card_response_handle($input_source);

      } else {

        $timestamp = time();
        while(time() - $timestamp < 300){
          get_queue($input_source);

          if($input_source["conn_type"] == 0x73){
            break;
          }

          sleep(100);
        }
      }


      $global -> funcs -> output(array($conn_type, 0xFB, ord($input_source[2][0]), ord($input_source[2][1]))); // 關閉通道


    }
  }

}




function card_response_handle($res){
  global $global;


  $serialNum = hexdec(sprintf("%02X", ord($res[4][0])).sprintf("%02X", ord($res[4][1])));

  $index = $res["index"]."_".$serialNum;
  $queue = $global -> redis -> get($index);


  if(empty($queue)){
    $waitting = $global -> redis -> get($index."_waitting");
    if(!empty($waitting)){
      $global -> redis -> set($index."_response", $res[5]);
    }
  } else {

    $apdu = $global -> map -> cmd_map($queue);
    if(!empty($apdu)){

      $new_apdu = cmd_data_handle($queue, $apdu[0], $res[5]);

      $Instruction = array_merge(array($res["conn_type"], 0xFC, ord($res[2][0]), ord($res[2][1]), ord($res[4][0]), ord($res[4][1]), $res["os"]), $new_apdu);
      
 

      if(!empty($apdu[1])){
        $global -> redis -> set($index, $apdu[1]);
      } else {
        $global -> redis -> del($index);
      }

      $global -> funcs -> output($Instruction);
      exit;
    } else {
      $global -> redis -> del($index); 
    }
  }
}



function cmd_data_handle($queue, $apdu, $data){

  $new_apdu = array();
  switch($queue){
    case "device_info_2":
      if(!empty($data)){

        $new_apdu = $apdu;
        $new_apdu[4] = ord($data);
      }
    break;

  }

  return $new_apdu;
}




function get_queue($req){
  global $global;
  
  $qkey = "";

  $iterator = null;
  while(false !== ($keys = $global -> redis -> scan($iterator, $req["index"]."_*"))) {
      foreach($keys as $key) {
        $k = explode("_", $key);
        $kc = array_pop($k);
        if($kc == "waitting"){
          continue;
        } else {
          $qkey = $key;
          break 2;
        }
      }
  }


  if(!empty($qkey)){
    $queue = $global -> redis -> get($qkey);
    if(!empty($queue)){
      $pre1 = array();
      $apdu = array();
      $rand = explode("_", $qkey);
      $token = $req[2];

      switch($queue){
        case "device_info": // 取得設備資訊

          $apdu = $global -> map -> cmd_map($queue);
          if(!empty($apdu)){
            $r1 = ord(chr($rand[2] >> 8));
            $r2 = ord(chr($rand[2]));

            
            $t1 = ord($req[2][0]);
            $t2 = ord($req[2][1]);
            

            $conqueue = array_merge(array($req["conn_type"], 0xFC, $t1, $t2, $r1, $r2, $req["os"]), $apdu[0]);
            $Instruction = array_merge($pre1, $conqueue);
            
            if(!empty($apdu[1])){
              $global -> redis -> set($qkey, $apdu[1]);
            } else {
              $global -> redis -> del($qkey);
            }
            
            $global -> funcs -> output($Instruction);
            exit;
          } else {
            $global -> redis -> del($qkey); 
          }
        break;
    
        case "change_pin": // 變更 pin 碼
    
        break;
    
        case "verify_pin": // 校驗 pin 碼
    
        break;
    
        case "unlock_pin": // 解鎖 pin 碼
    
        break;
    
        case "create_doc": // 建立文件
    
        break;
    
        case "select_doc": // 選擇文件
    
        break;
    
        case "delete_doc": // 刪除文件
    
        break;
    
        case "doc_info": // 取得文件屬性
    
        break;
    
        case "get_doc_read_id": // 取得 讀取文件的 id
    
        break;
    
        case "read_doc":  // 讀取文件內容
    
        break;
    
        case "get_doc_write_id": // 取得 寫文件的 id
    
        break;
    
        case "write_doc": // 將資料寫入文件
    
        break;
    
        case "get_rand": // 取得卡片隨機數
    
        break;
    
        case "create_key_pair": // 卡片產生密鑰對
    
        break;
    
        case "summary_d1": // 摘要 消息數據
    
        break;
    
        case "summary_d2": // 摘要 摘要數據
    
        break;
    
        case "rsa_d1": //  RSA 待運算
    
        break;
    
        case "rsa_d2": // RSA 運算結果
    
        break;
    
        case "hash_set":
    
        break;
    
        case "":
    
        break;
    
    
        default:
      }
    } else {
      $global -> redis -> del($qkey); 
    }
  }
}










