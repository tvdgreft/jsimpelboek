<?php
namespace SIMPELBOEK;
/**
 * prana_Ptext is gemaakt om de plugin eventueel later meertalig te maken.
 */
function prana_PText(string $short,string $long) : string 
{
	$html = '';
	$html .= $long;
	return($html);
}
function pranaAlert(string $message)
{
		echo "<script>alert('$message');</script>";
}
function  pranaConfirm(string $message){
    echo 
    "<script>
    var confirm='yes';
    var yes = 'yes';
    </script>";
    $var = "<script>document.write(confirm);</script>";
    $yes = "<script>document.write(yes);</script>";
    echo $var;
    if($var == $yes) {return(TRUE);}
    return (FALSE);
}
/**
     * Encrypts (but does not authenticate) a message
     * 
     * @param string $message - plaintext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encode - set to TRUE to return a base64-encoded 
     * @return string (raw binary)
     */
    function pranaEncrypt($message, $key)
    {
		$method = 'aes-256-ctr';
        $nonceSize = openssl_cipher_iv_length($method);
        $nonce = openssl_random_pseudo_bytes($nonceSize);

        $ciphertext = openssl_encrypt($message,$method,$key,OPENSSL_RAW_DATA,$nonce);
        return base64_encode($nonce.$ciphertext);
    }

    /**
     * Decrypts (but does not verify) a message
     * 
     * @param string $message - ciphertext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encoded - are we expecting an encoded string?
     * @return string
     */
    function pranaDecrypt($message, $key, $encoded = false)
    {
		$method = 'aes-256-ctr';
        $message = base64_decode($message, true);
        if ($message === false) 
		{
             throw new Exception('Encryption failure');
        }
        $nonceSize = openssl_cipher_iv_length($method);
        $nonce = mb_substr($message, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($message, $nonceSize, null, '8bit');

        $plaintext = openssl_decrypt($ciphertext,$method,$key,OPENSSL_RAW_DATA,$nonce);

        return $plaintext;
    }
    function pranaSendMail($to,$subject,$body,$attachement,$name)
{	
		#echo "mailto:" . $to . "<br>";
	$mailer = \JFactory::getMailer();
	$config = \JFactory::getConfig();
	$sender = array( $config->get( 'mailfrom' ),$config->get( 'fromname' ) );
	$mailer->setSender($sender);
	$mailer->addRecipient($to);
	$mailer->setSubject($subject);
	$mailer->setBody($body);
	$mailer->isHTML();
	#echo "attachement:" . $attachement . "<br>";
	if($attachement) { $mailer->addAttachment($attachement,$name); }
	#echo "start sending:" . $subject . "<br>";
	$send = $mailer->Send();
	#echo "done<br>";
	return($send);
}
#
# insert a log recordd
#
function pranaLog($args)
{
    $dbio = new DBIO;
	$dbio -> CreateTable($args["table"],Dbtables::logtable['columns']);
    $dbio-> CreateRecord(array("table"=>$args["table"],"fields"=>$args['fields']));
}
function pranaMenuLink($args)
{
    $dbio = new DBIO;
    $menu = $dbio->ReadRecord(array("table"=>"menu","id"=>$args["menu"]));
	$linkurl=\JRoute::_("index.php?Itemid={$args['menu']}");
    if($args["key"]) { $linkurl .= '?key=' . $args["key"]; }
    $text = isset($args["text"]) ? $args["text"] : $menu->title;
	$l = '<p><a href="' . $linkurl . '">' . $text . '</a></p>';
	return($l);
}
/**
 * get dutch dat like vrijdag 27 maart 1947
 * sefull when setlocale($time,"nl_NL") is not installed on server
 */
function Dutchdate($time)
{
	$arrayday = array("maandag","dinsdag","woensdag","donderdag","vrijdag","zaterdag","zondag");
	$arraymonth = array("januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
	$dayofmonth=date('j',$time);
	$day = date("N",$time) -1;
	$month = date("n",$time)-1;
	$year = date("Y",$time);
	$datum = $arrayday[$day] . ' ' . $dayofmonth . ' ' . $arraymonth[$month] . ' ' . $year;
	return($datum);
}