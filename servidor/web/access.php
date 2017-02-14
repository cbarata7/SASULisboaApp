<?php
include('Crypt/RSA.php');
header('Access-Control-Allow-Origin: *');
class RestRequest {

    private $request_vars;
    private $data;
    private $http_accept;
    private $method;

    public function __construct() {
        $this->request_vars = array();
        $this->data = '';
        $this->http_accept = 'json';
        $this->method = 'get';
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function setMethod($method) {
        $this->method = $method;
    }

    public function setRequestVars($request_vars) {
        $this->request_vars = $request_vars;
    }

    public function getData() {
        return $this->data;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getHttpAccept() {
        return $this->http_accept;
    }

    public function getRequestVars() {
        return $this->request_vars;
    }

}

class RestUtils {

    public static function processRequest() {
        // get our verb
        $request_method = strtolower($_SERVER['REQUEST_METHOD']);
        $return_obj = new RestRequest();
        // we'll store our data here
        $data = array();

        switch ($request_method) {
            // gets are easy...
            case 'get':
                $data = $_GET;
                break;
            // so are posts
            case 'post':
                $data = $_POST;
                break;
            // here's the tricky bit...
            case 'put':
                parse_str(file_get_contents('php://input'), $put_vars);
                $data = $put_vars;
                break;
        }

        // store the method
        $return_obj->setMethod($request_method);

        // set the raw data, so we can access it if needed (there may be
        // other pieces to your requests)
        $return_obj->setRequestVars($data);

        if (isset($data['data'])) {
            // translate the JSON to an Object for use however you want
            $return_obj->setData($data['data']);
        }
        return $return_obj;
    }

    public static function sendResponse($status = 200, $body = '', $content_type = 'text/html') {
        $status_header = 'HTTP/1.1 ' . $status . ' ' . RestUtils::getStatusCodeMessage($status);
        // set the status
        header($status_header);
        // set the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
            exit;
        }
        // we need to create the body if none is passed
        else {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status) {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templatized in a real-world solution
            $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
						<html>
							<head>
								<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
								<title>' . $status . ' ' . RestUtils::getStatusCodeMessage($status) . '</title>
							</head>
							<body>
								<h1>' . RestUtils::getStatusCodeMessage($status) . '</h1>
								<p>' . $message . '</p>
								<hr />
								<address>' . $signature . '</address>
							</body>
						</html>';

            echo $body;
            exit;
        }
    }

    public static function getStatusCodeMessage($status) {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );

        return (isset($codes[$status])) ? $codes[$status] : '';
    }

}



$data = RestUtils::processRequest();
				
$server = '';
$user = '';
$password = "";
$database = '';

$con = mysqli_connect($server, $user, $password, $database);
if (!$con) {
  die('Nao e possivel conectar a base de dados: ' . mysqli_error($con));
}		

$arr = array(0 => "Refeitório Um", 
1 => "Refeitório de Belas Artes", 
2 => "Refeitório de Ciências", 
3 => "Refeitório do ISA", 
4 => "Refeitório do Taguspark", 
5 =>"Snack Bar Palácio Burnay",
6 =>"Refeitório do IST (Alameda)",
7 =>"Refeitório do ISEG",
8 =>"Refeitório do Pólo da Ajuda",
9 => "Refeitório F.M.H.");
						
switch ($data->getMethod()) {
    case 'get':
		$url = split("access.php", $_SERVER['REQUEST_URI']);
		$url = split("/", $url[1]);
        if(count($url) == 5){
            $result = mysqli_query($con, "SELECT * FROM view_fc_users_movs WHERE chveuser='".$url[3]."' ORDER BY data DESC");
            while($row = mysqli_fetch_assoc($result)) { 
                $aux [] = array_map('utf8_encode', $row);
            }
        }
        else if(count($url) == 7 & $url[1] !== "login" && $url[2] !== "user" && $url[4] !== "pass"){
                $result = mysqli_query($con, "SELECT * FROM c1portal_db.view_fc_ementas WHERE 
                data ='".$url[3]."' AND pt_refeitorio='".utf8_decode($arr[$url[5]])."';");

                while($row = mysqli_fetch_assoc($result)) { 
                    $cenas = array(data => $row['data'], pt_refeitorio => $row['pt_refeitorio'], pt_tipo => $row['pt_tipo'], pt_nome => $row['pt_nome'], pt_refeicao => $row['pt_refeicao'], pt_menu => $row['pt_menu'], alergeneos_PT =>$row['alergeneos_PT'], en_refeitorio => $row['en_refeitorio'], en_tipo => $row['en_tipo'], en_nome => $row['en_nome'], en_refeicao => $row['en_refeicao'], en_menu => $row['en_menu'], alergeneos_EN =>$row['alergeneos_EN'] );
                    //$aux [] = array_map('utf8_encode', $cenas);
                    $aux [] = array_map('utf8_encode', $cenas);
                }
        }
		else if(count($url) == 9 && $url[1] === "refeicoes" && $url[2] === "data" && $url[4] === "refeitorio" && $url[6] === "lingua"){
			$result = mysqli_query($con, "SELECT * FROM c1portal_db.view_fc_ementas WHERE 
				data ='".$url[3]."' AND pt_refeitorio='".utf8_decode($arr[$url[5]])."';");
				
			if($url[7] === "pt"){		
				while($row = mysqli_fetch_assoc($result)) {	
				//print_r($row);
					$cenas = array(data => $row['data'], pt_refeitorio => $row['pt_refeitorio'], pt_tipo => $row['pt_tipo'], pt_nome => $row['pt_nome'], pt_refeicao => $row['pt_refeicao'], pt_menu => $row['pt_menu'], alergeneos_PT =>$row['alergeneos_PT'] );
					//$aux [] = array_map('utf8_encode', $cenas);
					$aux [] = array_map('utf8_encode', $cenas);
				}
			}
			else if($url[7] === "en"){		
				while($row = mysqli_fetch_assoc($result)) {	
				//print_r($row);
					$cenas = array(data => $row['data'], en_refeitorio => $row['en_refeitorio'], en_tipo => $row['en_tipo'], en_nome => $row['en_nome'], en_refeicao => $row['en_refeicao'], en_menu => $row['en_menu'], alergeneos_EN =>$row['alergeneos_EN']);
					//$aux [] = array_map('utf8_encode', $cenas);
					$aux [] = array_map('utf8_encode', $cenas);
				}
			}
		}
	
		else if(/**count($url) == 7 &&**/ $url[1] === "login" && $url[2] === "user" && $url[4] === "pass"){
			$pass_join = $url[5];
			for($i=1; $i<count($url)-6;$i++){
				$pass_join = $pass_join . "/" . $url[(5+$i)];
			}
			$aux_pass= explode("&", $pass_join );
			$encrypted = base64_decode($aux_pass[0]);
			$iv        = base64_decode($aux_pass[1]);   
			$key       = base64_decode($aux_pass[2]);

			$plaintext = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_128, $key, $encrypted, MCRYPT_MODE_CBC, $iv ), "\t\0 " );
			
			if (strpos($url[3],'@campus.ul.pt') !== false){
				$aux2 = explode("@",$url[3]);
				$username = $aux2[0];
				$res = exec("curl https://www.google.com/accounts/ClientLogin --data-urlencode Email=".$url[3]." --data-urlencode Passwd=".$plaintext." -d accountType=GOOGLE -d source=Google-cURL-Example -d service=lh2");
			}
			else{
				$res = exec("curl https://www.google.com/accounts/ClientLogin --data-urlencode Email=".$url[3]."@campus.ul.pt --data-urlencode Passwd=".$plaintext." -d accountType=GOOGLE -d source=Google-cURL-Example -d service=lh2");
				$username = $url[3];
			}
			if($res !== "Error=BadAuthentication"){
				$result = mysqli_query($con, "SELECT * FROM c1portal_db.view_fc_users WHERE login = '".$username ."';");
				while($row = mysqli_fetch_assoc($result)) {	
					$aux [] = array_map('utf8_encode', $row);
				}
			}
			else{
				$aux [] = "failed";
			}
			//$result = mysqli_query($con, "SELECT * FROM c1portal_db.view_fc_users");
			/*while($row = mysqli_fetch_assoc($result)) {	
					$aux [] = array_map('utf8_encode', $row);
				}*/
		}
		$res=str_replace(array( '[', ']' ), '', $aux);			
		$jsonp = $_GET[ 'jsoncallback' ];
		if ( isset($jsonp) ) {
			$res_str =  $jsonp . '(' . json_encode($res) . ');';
			$type = 'application/javascript';
		} 
		else{
			$res_str = json_encode($res);
			$type = 'application/json';
		}
		RestUtils::sendResponse(200, $res_str, $type);
		break;

}
    function url_decode($string){
        return utf8_decode(urldecode($string));
    }
		
	/* array sort helper function */
	function randomSort($a, $b){
		return rand(-1, 1);
	}
	function gradeFrequency($frequency){
		$grade = 0;
		if ($frequency >= 90)
			$grade = 9;
		else if ($frequency >= 70)
			$grade = 8;
		else if ($frequency >= 60)
			$grade = 7;
		else if ($frequency >= 50)
			$grade = 6;
		else if ($frequency >= 40)
			$grade = 5;
		else if ($frequency >= 30)
			$grade = 4;
		else if ($frequency >= 20)
			$grade = 3;
		else if ($frequency >= 10)
			$grade = 2;
		else if ($frequency >= 5)
			$grade = 1;
		return $grade;
	}
	
?>
