<?php
require_once 'include/jsonRpcClient.php';
require_once 'include/utils.php';

$netfee = 0.001;
$path = "/home/t2bc/text2bc/"; 
$usedUTXOs = $path.'usedUTXOs.txt'; //UTXO used
$newTXs = $path.'newTXs.txt'; //NEW TX
$newSTXs = $path.'newSTXs.txt'; //Signed TX
$sentTXs = $path.'sentTXs.txt'; //Sent TXs
$ownTDCaddrr = "aA22Zs5xWdmaSZnMngPHZ4ubqpzwx1FwFA"; //узенең TDCoin адреска алыштырыгыз

$home = getenv("HOME");
$conf = parse_ini_file($home."/.tdcoin/tdcoin.conf");
if(!array_key_exists('rpcuser',$conf)||!array_key_exists('rpcpassword',$conf)){
    $userpass = $conf['rpcuser'].":".$conf['rpcpassword'];
}
else $userpass = $conf['rpcuser'].":".$conf['rpcpassword'];
$tdcoin = new Jsonrpcclient(array('url'=>'http://'.$userpass.'@127.0.0.1:9902/','debug'));

$inputtxt = file($path."text.txt");
$index = 0;
foreach($inputtxt as $line){
    $t = hexdump(trim($line));
    if(strlen( $t ) > 80) die("Line is too long!"); //Length of hex string must not exceed 80 bytes!!!
    $hx[] = $t;
//    echo "index:$index\tlength:".strlen($hx)."\t".$hx.PHP_EOL;
//    $index++;
}
//print_r($hx);

//print_r($input);

$un = $tdcoin->listunspent(2,999,array($ownTDCaddrr)); 
foreach($un as $untxo){
    echo "txid: ".$untxo['txid'].PHP_EOL;
    echo "vout: ".$untxo['vout'].PHP_EOL;
    echo "change: ".($untxo['amount']-$netfee).PHP_EOL.PHP_EOL;
    if($untxo['amount']-$netfee>0) $goodtx[]=array('txid'=>$untxo['txid'],'vout'=>$untxo['vout'],'change'=>$untxo['amount']-$netfee);
}

if( count($goodtx) <= count($hx) ) die( "Not enough UTXOs!");

$ind =0;
file_put_contents($usedUTXOs,'');
file_put_contents($newTXs,'');
file_put_contents($newSTXs,'');
file_put_contents($sentTXs,'');

foreach($hx as $ln){
    $input  = array(array('txid'=>$goodtx[$ind]['txid'],'vout'=>$goodtx[$ind]['vout']));
    $output = array( 'data'=>$ln, $ownTDCaddrr=>$goodtx[$ind]['change'] );
//var_dump($input); var_dump($output);
    $newtxid = $tdcoin->createrawtransaction( $input, $output );
    if(is_array($newtxid) && array_key_exists('code',$newtxid)){
        die( "Error: ".$newtxid['message'].PHP_EOL );
    }else{
        file_put_contents($usedUTXOs,$goodtx[$ind]['txid'].PHP_EOL, FILE_APPEND | LOCK_EX );
        file_put_contents($newTXs,$newtxid.PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    var_dump($newtxid);
    sleep(1); $ind++;
}

$trans = file($newTXs);
if(count($trans)>0){
    foreach($trans as $tran){
        $sgnTrans = $tdcoin->signrawtransactionwithwallet( trim($tran) );
        if(is_array($sgnTrans) && array_key_exists('code',$sgnTrans)) { var_dump($sgnTrans); die("Error: ".$sgnTrans['message'].PHP_EOL); }
        if($sgnTrans['complete'] != 'true') die("Error: Transaction signature failed".PHP_EOL);
        file_put_contents($newSTXs,$sgnTrans['hex'].PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

$sends = file($newSTXs);
if(count($sends)<1) die("Error: Nothing to send".PHP_EOL);
foreach($sends as $send){
    $sendTrans = $tdcoin->sendrawtransaction( trim($send) );
    var_dump($sendTrans);
    file_put_contents($sentTXs,$sendTrans.PHP_EOL, FILE_APPEND | LOCK_EX );
    sleep(1);
}
//var_dump($goodtx);
