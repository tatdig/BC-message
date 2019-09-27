<?php
require_once 'include/jsonRpcClient.php';
require_once 'include/utils.php';
require_once 'include/colored.php';

$colors = new Colors();
$netfee = 0.0001; //TDC
$datasize = 1020; //bytes
$waddr = "Your local wallet address here"

$usedUTXOs = '/home/tdcoincore/text2bc/usedUTXOs.txt'; //UTXO used
$newRawTXs = '/home/tdcoincore/text2bc/newRawTXs.txt'; //NEW TX
$newSTXs = '/home/tdcoincore/text2bc/newSTXs.txt'; //Signed TX
$sentTXs = '/home/tdcoincore/text2bc/sentTXs.txt'; //Sent TXs

$home = getenv("HOME");
$conf = parse_ini_file($home."/.tdcoin/tdcoin.conf");
if(!array_key_exists('rpcuser',$conf)||!array_key_exists('rpcpassword',$conf)){
    $userpass = $conf['rpcuser'].":".$conf['rpcpassword'];
}
else $userpass = $conf['rpcuser'].":".$conf['rpcpassword'];
$tdcoin = new Jsonrpcclient(array('url'=>'http://'.$userpass.'@127.0.0.1:9902/','debug'));

$inputtxt = file("/home/tdcoincore/text2bc/text.txt");
$index = 0;
foreach($inputtxt as $line){
    if(empty(trim($line))) continue;
    $t = hexdump($line);
    if(strlen( $t ) > $datasize) die("Line is too long!");
    $hx_[] = $t;
//    echo "index:$index\tlength:".strlen($hx)."\t".$hx.PHP_EOL;
//    $index++;
}

$changed = true;

while($changed){
    $changed = false;
    for($ii=0; $ii< count($hx_); $ii++){
    echo $ii.PHP_EOL;
	if(array_key_exists($ii+1,$hx_) && strlen($hx_[$ii].$hx_[$ii+1])<$datasize+1) {
	    $hx__[]=$hx_[$ii].$hx_[$ii+1];
	    $changed = true;
	    $ii++;
	} else $hx__[]=$hx_[$ii];
    }
    if($changed){
	unset($hx_);
	$hx_ = $hx__;
	unset($hx__);
    }
}

$hx = $hx_;

$un = $tdcoin->listunspent(2,999,array($waddr));
var_dump($un);
foreach($un as $untxo){
    echo "txid: ".$untxo['txid'].PHP_EOL;
    echo "vout: ".$untxo['vout'].PHP_EOL;
    echo "change: ".($untxo['amount']-$netfee).PHP_EOL.PHP_EOL;
    if($untxo['amount']-$netfee>0) $goodtx[]=array('txid'=>$untxo['txid'],'vout'=>$untxo['vout'],'change'=>$untxo['amount']-$netfee);
}

if( count($goodtx) <= count($hx) ) die( $colors->getColoredString("Not enough UTXOs!",'red',null).PHP_EOL );

$ind =0;
file_put_contents($usedUTXOs,'');
file_put_contents($newRawTXs,'');
file_put_contents($newSTXs,'');
file_put_contents($sentTXs,'');

foreach($hx as $ln){
    $input  = array(array('txid'=>$goodtx[$ind]['txid'],'vout'=>$goodtx[$ind]['vout']));
    $output = array( 'data'=>$ln, $waddr => number_format($goodtx[$ind]['change'],4,'.','') );
var_dump($input); var_dump($output);
    $newrawtx = $tdcoin->createrawtransaction( $input, $output );
    if(is_array($newrawtx) && array_key_exists('code',$newrawtx)){
	var_dump($newrawtx);
	die( "Error: ".$newrawtx['message'].PHP_EOL );
    }else{
	echo PHP_EOL."$newrawtx - newrawtx".PHP_EOL;
	if(file_put_contents($usedUTXOs,$goodtx[$ind]['txid'].PHP_EOL, FILE_APPEND | LOCK_EX )===false) die($colors->getColoredString("Error writing to $usedUTXOs",'red',null).PHP_EOL);
	if(file_put_contents($newRawTXs, $newrawtx.PHP_EOL, FILE_APPEND | LOCK_EX)===false) die($colors->getColoredString("Error writing to $newTXs",'red',null).PHP_EOL);
    }
    sleep(1); $ind++;
}

$trans = file($newRawTXs);
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