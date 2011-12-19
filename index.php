<?php
session_start();
$className = 'index';
if(!empty($_GET)) {
  $className = key($_GET);
} 

$obj = new $className();

abstract class mongo_data {
	
	protected $db;
	protected $collection;
	protected $cursor;
	protected $record_id;
	protected $temp;
	protected $record;
	
	protected function mconnect() {
		$username = 'kwilliams';
		$password = 'mongo1234';
		$this->connection = new Mongo("mongodb://${username}:${password}@localhost/test",array("persist" => "x"));
		$this->setDb();
	}
	protected function setDb($db = 'default1') {
		$this->db = $this->connection->$db;
	}
	protected function setCollection($collection) {
		$this->collection = $this->db->$collection;
		
	}
	protected function findRecords($query = null) {
		if($query == null) {
			$this->cursor = $this->collection->find();
		} else {
			$this->cursor = $this->collection->find($query);
		}
		return $this->cursor;
	}
	
	protected function findRecord($query = null) {
		if($query == null) {
			$this->record = $this->collection->findOne();
		} else {
			$this->record = $this->collection->findOne($query);
		}
		return $this->record;
	}
	
	protected function add($query) {
		$this->collection->insert($query);
		$this->record_id = $query;
		$this->cursor = $this->collection->find();
		
	}
	
	protected function getRecord() {
		foreach($this->record as $key => $value) {
				
				$this->temp .= $key . ': ' . $value . "<br>\n";
				
			}		
			$this->temp .= '<hr>';
		return $this->temp;
	}

	protected function update($query) {
		$this->collection->update($query);
	}
	protected function delete($query) {
		
	}
	protected function getRecords() {
			
		foreach($this->cursor as $record) {
			foreach($record as $key => $value) {
				
				$this->temp .= $key . ': ' . $value . "<br>\n";
				
			}		
			$this->temp .= '<hr>';
		}
		return $this->temp;
	}
 	protected function getRecordID() {
 		return $this->record_id;
 	}
}
abstract class data extends mongo_data {
	protected $query;
	protected $connection;
}
abstract class request extends data {
	protected $data;
	protected $form;
	 function __construct() {
	 	
		if($_SERVER['REQUEST_METHOD'] == 'GET') {
			$this->get();

		} else {
			
			$this->post();
		}
		$this->display();
	}
	protected function get() {
		// gets the first value of the $_GET array, so that the correct form function is called.
		$function = array_shift($_GET) . '_get';
		$this->$function();
	}
	protected function post() {
		// gets the first value of the $_GET array, so that the correct form function is called.
		$function = array_shift($_GET) . '_post';
		$this->$function();
	}
}
//this is the class for the homepage

abstract class page extends request {
	protected $header;
	protected $content;
	protected $footer;
	
	protected function display() {
		echo $this->setHeader();
		echo $this->content;
		echo $this->setFooter();
	}

	protected function setHeader() {
		$this->header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
						 "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
						 <html xmlns="http://www.w3.org/1999/xhtml">
						<head>
							<title>PHP Final</title>
							<style type = "text/css">
							body 
							{
							background-color: #F9F99F;
							}
							h1
							{
							font-weight: bold;
							font-family: Rockwell, monospace;
							}
							#main
							{
							padding-left: 60px;
							}
							a
							{
							text-decoration: none;
							}
							a:hover
							{
							background: #FFCC00;
							}
							</style>
						</head>
						<body>';
		return $this->header;
	}

	protected function setFooter() {
		$this->footer = '</body>
					    </html>';
		return $this->footer;
	
	}
}

class index extends page {
	function __construct() {
		parent::__construct();
	}

	protected function get() {

		$this->content = '<h1>Welcome To The App</h1>';
		$this->content .= '<div id="main">';
		$this->content .= '<a href="index.php?people=login">Click Here To Login</a><br>';
		$this->content .= '<a href="index.php?people=signup">Click Here To Signup</a><br>';
		$this->content .= '<a href="index.php?people=directory">Click Here To View Users</a><br>';
		$this->content .= '<a href="index.php?people=user">Click Here To View Your Account</a><br>';
		$this->content .= '<a href="index.php?people=forgot">Forgot Your Password?</a><br>';
		$this->content .= '<a href="index.php?survey=country">Random Question</a><br>';
		$this->content .= '</div>';
	
	}
}
//this will handle logins

class people extends page {
	function __construct() {
		$this->mconnect();
		$this->setCollection('people');
		parent::__construct();
	}

	protected function login_get() {
	
		$this->content = '<h1>Login Here</h1>';
		$this->content .= $this->login_form();
	
	}
	
	protected function login_form() {
		
			$this->form = '<FORM action="./index.php?people=login" method="post">
    				   <LABEL for="username">Username: </LABEL>
              		   <INPUT name="username" type="text" id="username"><BR>
    		           <LABEL for="password">Password: </LABEL>
                       <INPUT name="password" type="password" id="password"><BR>
                       <INPUT type="submit" value="Login"> <INPUT type="reset"></br>
                       <a href="./index.php?people=signup">Click To Signup</a>
 					   </FORM>';
		return $this->form;
	
	}
	protected function login_post() {
		
		$this->findRecord(array('username' => $_POST['username']));
		echo $_SESSION['username'];
		
		$this->content .= '<a href="index.php?people=user">Click Here To View Your Account</a><br>';
	
	}
	protected function signup_get() {
		$this->content = '<h1>Signup Here</h1>';
		$this->content .= $this->signup_form();
		
	}
	protected function signup_form() {
		$this->form = '<FORM action="./index.php?people=signup" method="post">
    				   <LABEL for="firstname">First name: </LABEL>
              		   <INPUT type="text" name="fname" id="firstname"><BR>
    				   <LABEL for="lastname">Last name: </LABEL>
              		   <INPUT type="text" name="lname" id="lastname"><BR>
    				   <LABEL for="email">Email: </LABEL>
              		   <INPUT type="text" name="email" id="email"><BR>
					   <LABEL for="password">Password: </LABEL>
              		   <INPUT type="password" name="password" id="password"><BR>
              		   <LABEL for="zip">Zip Code: </LABEL>
              		   <INPUT type="text" name="zip" id="zip"><BR>
              		   <INPUT type="submit" value="Send"> <INPUT type="reset">
    				   </P>
				   	   </FORM>';
		return $this->form;			  
	}
	protected function signup_post() {
		$this->add($_POST);
		$this->getRecordID();
		$this->content .= '<a href="index.php?people=login">Click Here To Login</a><br>';
		$this->content .= '<a href="index.php?people=directory">Click Here To View Users</a><br>';
	}
	protected function directory_get() {
		$this->content = '<h1>User Accounts</h1>';
		$this->findRecords();
		$this->content .= $this->getRecords();
	}
	
	protected function user_get() {
		$this->findRecord(array('username' => $_SESSION['username']));
		$this->content = $this->getRecord();
	}
	
	protected function forgot_get() {
	
		$this->content = '<h1>New Password</h1>';
		$this->content .= $this->forgot_form();
	}
	protected function forgot_post() {
				
		$this->content .= '<a href="index.php?people=login">Click Here To Login</a><br>';
		$this->content .= '<a href="index.php?people=directory">Click Here To View Users</a><br>';
	}
	protected function forgot_form() {
		$this->form = '<FORM action="./index.php?people=forgot" method="post">
              		   <LABEL for="email">Email:</LABEL>
              		   <INPUT type="text" name="email" id="email"><BR>
    				   <INPUT type="submit" value="Send New Password">
    				   </P>
				   	   </FORM>';
		return $this->form;	

	}
}

class survey extends page {
	function __construct() {
		$this->mconnect();
		$this->setCollection('country');
		parent::__construct();
	}
	
	protected function country_get() {
	
		$this->content = '<h1>What is your country of origin?</h1>';
		$this->content .= $this->country_form();
	}

	protected function country_form() {
	
	$this->form = '<FORM action="index.php?survey=country" method="post">
					<LABEL for="country">Country:</LABEL>
					<INPUT type="text" name="country" id="country"><BR>
					<input type="submit" value="Submit" />
					</FORM>';
	return $this->form;
	}
	
	protected function country_post() {
		$this->add($_POST);
		$this->content .= $this->getRecords();
		
		
	}

}

echo '<h1>State Data</h1>';
$states = new states();

if(!$_GET)	{

	foreach($states->state_list as $abv => $state) {

		echo '<a href="stateabbr.php?q=' . strtolower($abv) . '">' . $state . "</a> </br> \n";

	}

} else	{
	
	$state = new statedata($_GET['q']);
	
	foreach($state->data as $city)	{
	
		echo '<div id="city">';
		foreach($city as $key => $value)	{
			
			echo '<span class=" city field ' . $key . '">' . $key . ': ' . $value . "</span><br> \n";
		
		}
		echo '</div>';
		echo '</p>';
		
	}
}

class states {
	public $state_list = array('AL'=>"Alabama",
                'AK'=>"Alaska", 
                'AZ'=>"Arizona", 
                'AR'=>"Arkansas", 
                'CA'=>"California", 
                'CO'=>"Colorado", 
                'CT'=>"Connecticut", 
                'DE'=>"Delaware", 
                'DC'=>"District Of Columbia", 
                'FL'=>"Florida", 
                'GA'=>"Georgia", 
                'HI'=>"Hawaii", 
                'ID'=>"Idaho", 
                'IL'=>"Illinois", 
                'IN'=>"Indiana", 
                'IA'=>"Iowa", 
                'KS'=>"Kansas", 
                'KY'=>"Kentucky", 
                'LA'=>"Louisiana", 
                'ME'=>"Maine", 
                'MD'=>"Maryland", 
                'MA'=>"Massachusetts", 
                'MI'=>"Michigan", 
                'MN'=>"Minnesota", 
                'MS'=>"Mississippi", 
                'MO'=>"Missouri", 
                'MT'=>"Montana",
                'NE'=>"Nebraska",
                'NV'=>"Nevada",
                'NH'=>"New Hampshire",
                'NJ'=>"New Jersey",
                'NM'=>"New Mexico",
                'NY'=>"New York",
                'NC'=>"North Carolina",
                'ND'=>"North Dakota",
                'OH'=>"Ohio", 
                'OK'=>"Oklahoma", 
                'OR'=>"Oregon", 
                'PA'=>"Pennsylvania", 
                'RI'=>"Rhode Island", 
                'SC'=>"South Carolina", 
                'SD'=>"South Dakota",
                'TN'=>"Tennessee", 
                'TX'=>"Texas", 
                'UT'=>"Utah", 
                'VT'=>"Vermont", 
                'VA'=>"Virginia", 
                'WA'=>"Washington", 
                'WV'=>"West Virginia", 
                'WI'=>"Wisconsin", 
                'WY'=>"Wyoming");
	}
	
	class service extends page	{
		public $data;
		protected $response_format;
		public $url;
		
		public function request ()	{
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$results = curl_exec($ch);
		if(json_decode(curl_exec($ch)) === NULL) {
			$this->data = new SimpleXmlElement($results);
		} else {
			$this->data = json_decode(curl_exec($ch));
		}
		
		curl_close($ch);
		}
	}
	
	class rssreader extends service {
	
		function __construct() {
		$this->response_format = 'xml';
		}
		
		public function setFeed($feed) {
		$this->url = $feed;
		}
	
	}
	
	class statedata extends service	{
	
		public $url = "http://api.sba.gov/geodata/city_county_links_for_state_of/"; 
		
		function __construct($state)	{
			$this->url .= $state;
			$this->url .= '.xml';
			$this->request($this->url);
		}
	}





?>