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
				
$server = 'sas-websrv01.sas.ul.pt';
$user = 'fcul_labs1';
$password = "De32%rTul";
$database = 'c1portal_db';

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
8 =>"Refeitório do Pólo da Ajuda");
						
switch ($data->getMethod()) {
    case 'get':
		$url = split("access2.php", $_SERVER['REQUEST_URI']);
		$url = split("/", $url[1]);
		if(count($url) == 3){
			$result = mysqli_query($con, "SELECT * FROM view_fc_refs_aloj WHERE chvp='".$url[1]."'");
			//$result = mysqli_query($con, "SELECT * FROM c1portal_db.view_fc_users_movs WHERE chveuser='56025' ORDER BY data DESC");
		}
		else{
			$result = mysqli_query($con, "SELECT * FROM c1portal_db.view_fc_users");
			//$result = mysqli_query($con, "SELECT DISTINCT pt_refeitorio FROM c1portal_db.view_fc_ementas");
		}
		while($row = mysqli_fetch_assoc($result)) {	
			$aux [] = array_map('utf8_encode', $row);
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
