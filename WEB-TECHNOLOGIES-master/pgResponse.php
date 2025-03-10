<?php
session_start();
header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");

// following files need to be included
require_once("./lib/config_paytm.php");
require_once("./lib/encdec_paytm.php");

$paytmChecksum = "";
$paramList = array();
$isValidChecksum = "FALSE";

$paramList = $_POST;
$paytmChecksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : ""; //Sent by Paytm pg
//Verify all parameters received from Paytm pg to your application. Like MID received from paytm pg is same as your application�s MID, TXN_AMOUNT and ORDER_ID are same as what was sent by you to Paytm PG for initiating transaction etc.
$isValidChecksum = verifychecksum_e($paramList, PAYTM_MERCHANT_KEY, $paytmChecksum); //will return TRUE or FALSE string.
if($isValidChecksum == "TRUE") {
	echo "<b>Checksum matched and following are the transaction details:</b>" . "<br/>";
	if ($_POST["STATUS"] == "TXN_SUCCESS") {
		echo "<b>Transaction status is success</b>" . "<br/>";
		//Process your transaction here as success transaction.
		//Verify amount & order id received from Payment gateway with your application's order id and amount.
	}
	else {
		echo "<b>Transaction status is failure</b>" . "<br/>";
	}

	if (isset($_POST) && count($_POST)>0 )
	{ 
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "library";
        
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
        } 
        
        $sql = "INSERT INTO response (StudentId,MID,TXNID,TXNAMOUNT,PAYMENTMODE,CURRENCY,TXNDATE,STATUS,RESPCODE,RESPMSG,BANKTXNID,BANKNAME,CHECKSUMHASH,GATEWAYNAME)
        VALUES ('".$_POST['ORDERID']."','".$_POST['MID']."', '".$_POST['TXNID']."','".$_POST['TXNAMOUNT']."','".$_POST['PAYMENTMODE']."','".$_POST['CURRENCY']."','".$_POST['TXNDATE']."','".$_POST['STATUS']."','".$_POST['RESPCODE']."','".$_POST['RESPMSG']."','".$_POST['BANKTXNID']."','".$_POST['BANKNAME']."','".$_POST['CHECKSUMHASH']."','".$_POST['GATEWAYNAME']."')";
        
        if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
        $sid = $_SESSION['stdid'];
        $fin = $_SESSION['fines'];
        echo $fin;
        $fin = $fin-intval($_POST['TXNAMOUNT']);
        echo $fin;
        $bisbn = $_SESSION['bisbn'];
        $query = "UPDATE tblissuedbookdetails set fine = ".$fin." where ISBNNumber = '".$bisbn."' and StudentID = '".$sid."'";
        echo $query;
        $conn = mysqli_connect("localhost","root","","library");
        mysqli_query($conn,$query);
        } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
        }
        
        $conn->close();
	    
		foreach($_POST as $paramName => $paramValue) {
				echo "<br/>" . $paramName . " = " . $paramValue;
		}
	}
	

}
else {
	echo "<b>Checksum mismatched.</b>";
	//Process transaction as suspicious.
}

?>