<?php
/*
Plugin Name: PFC Training 
Plugin URI: http://www.pfcom.org/
Description: PFC training module
Version: 1.0
Author: OCC.org Technology Team
Author URI: http://www.occ.org/
License: GPL
*/

function pfctrain_install () 
{
	global $wpdb;
	
	// password is a 40 hex character SHA-1 hash
	$table_name = $wpdb->prefix . "pfctraining_users"; 
	$sqlAddLoginTable = "CREATE TABLE " . $table_name . " (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		first varchar(50),
		last varchar(50),
		username varchar(50),
		password varchar(40),
		email varchar(100),
		address varchar(200),
		city varchar(50),
		state varchar(3),
		zip varchar(10),
		country varchar(20),
		registered timestamp DEFAULT CURRENT_TIMESTAMP,
		UNIQUE KEY id (id)
		);";

	$sqlAddCourseTable = "CREATE TABLE " . $wpdb->prefix . "pfctraining_courses (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		number int,
		length int,
		type varchar(50),
		title varchar(50),
		description text,
		audiolink varchar(255),
		pdflink varchar(255),
		testlink varchar(255),
		UNIQUE KEY id (id)
		);";

	$sqlAddCourseTakenTable = "CREATE TABLE " . $wpdb->prefix . "pfctraining_coursestaken (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		courseid int, 
		userid int,
		media varchar(10),
		takenon timestamp DEFAULT CURRENT_TIMESTAMP,
		UNIQUE KEY id (id)
		);";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sqlAddLoginTable);
	dbDelta($sqlAddCourseTable);
	dbDelta($sqlAddCourseTakenTable);
}

function pfctrain_replaceshortcodes ($content)
{
	global $wpdb;

	if (strpos($content, "[pfctraining-login]") !== false)
	{
		$loginform = "<form method=\"post\">" .
				"E-mail Address<br />" .
				"<input type=\"text\" name=\"email\" size=\"40\"><br />" .
				"Password<br />" .
				"<input type=\"password\" name=\"password\"><br />" .
				"<input type=\"submit\" name=\"action\" value=\"Log in\"> or " .
				"<input type=\"submit\" name=\"action\" value=\"Register\">";
		$preserve = false;
		$body = '';
		if ($_POST["action"] == "Log in")
		{
			$query = 'select id, first, last from '. $wpdb->prefix . 'pfctraining_users where email="' . $_POST["email"] . '" and password="' . sha1($_POST["password"]) . '"';
			$data = $wpdb->get_row($query, ARRAY_A);
			if ($data == null)
			{
				$body = '<h2>Login failed</h2>
				<p>Sorry, but we couldn\'t find a user registration with the e-mail address <em>' . $_POST["email"] . '</em> and the given password.  Try again below.</p>';
				$body = $body . $loginform;
			}
			else
			{
				$_SESSION["loggedinuser"] = $data["first"] . ' ' . $data["last"];
				$_SESSION["loggedinuserid"] = $data["id"];
			}
		}
		if ($_POST["action"] == "Submit Registration")
		{
			$query = 'select id from ' . $wpdb->prefix . 'pfctraining_users where email="' . $_POST["user_email"] . '"';
			$row = $wpdb->get_row($query, ARRAY_A);
			if ($row)
			{
				$body = '<p>Sorry, <em>' . $_POST["user_email"] . '</em> is already registered.</p>';
			}
			else
			{
				$wpdb->insert($wpdb->prefix . 'pfctraining_users', 
				array ('first' => $wpdb->escape($_POST["user_first"]),
				'last' => $wpdb->escape($_POST["user_last"]),
				'password' => sha1($_POST["user_password"]),
				'email' => $wpdb->escape($_POST["user_email"]),
				'address' => $wpdb->escape($_POST["user_address"]),
				'city' => $wpdb->escape($_POST["user_city"]),
				'state' => $wpdb->escape($_POST["user_state"]),
				'zip' => $wpdb->escape($_POST["user_zip"]),
				'country' => $wpdb->escape($_POST["user_country"])));
				$body = 'Registration successful. Welcome to PFC Online Training, ' 
					. $_POST["user_first"]
					. ' '
					.  $_POST["user_last"]
					. '!' 
					. '<form method="post"><input type="submit" name="action" value="Proceed to Courses" /></form>';

				$_SESSION["loggedinuser"] = $_POST["user_first"] . ' ' . $_POST["user_last"];
				$_SESSION["loggedinuserid"] = $wpdb->insert_id;
			}
		}
		if ($_POST["action"] == "Register")
		{
			$body = '<script>
			function validatePassword()
			{
				var p1 = document.getElementById("pass1");
				var p2 = document.getElementById("pass2");
				var inval = document.getElementById("invalidpass");
				var reg = document.getElementById("regbutton");
				if (p1.value != p2.value)
				{
					inval.style.display = "inline";
					reg.disabled = true;
				}
				else
				{
					inval.style.display = "none";
					reg.disabled = false;
				}
			}
			</script>
			<h2>Register for Online Training</h2>
			<form method="post">
			<table border="0" style="border: 0px;">
			<tr><td>First name</td><td><input type="text" name="user_first" size="15"/></td></tr>
			<tr><td>Last name</td><td><input type="text" name="user_last" size="15"/></td></tr>
			<tr><td>Password</td><td><input type="password" id="pass1" name="user_password" value="' . $_POST["password"] . '" onkeyup="validatePassword()"/></td></tr>
			<tr><td>Password (again)</td><td><input type="password" id="pass2" name="user_password2" onkeyup="validatePassword()"/><br /><span id="invalidpass" style="font-weight:bold;color:red;display:none;">Passwords do not match</span></td></tr>
			<tr><td>E-mail address</td><td><input type="text" name="user_email" size="30" value="' .$_POST["email"] . '"/></td></tr>
			<tr><td>Street address</td><td><input type="text" name="user_address" size="30"/></td></tr>
			<tr><td>City, State ZIP</td><td><input type="text" name="user_city" size="15"/>, 
				<select name="user_state">
				<option value="AL">AL</option>
				<option value="AK">AK</option>
				<option value="AZ">AZ</option>
				<option value="AR">AR</option>
				<option value="CA">CA</option>
				<option value="CO">CO</option>
				<option value="CT">CT</option>
				<option value="DE">DE</option>
				<option value="FL">FL</option>
				<option value="GA">GA</option>
				<option value="HI">HI</option>
				<option value="ID">ID</option>
				<option value="IL">IL</option>
				<option value="IN">IN</option>
				<option value="IA">IA</option>
				<option value="KS">KS</option>
				<option value="KY">KY</option>
				<option value="LA">LA</option>
				<option value="ME">ME</option>
				<option value="MD">MD</option>
				<option value="MA">MA</option>
				<option value="MI">MI</option>
				<option value="MN">MN</option>
				<option value="MS">MS</option>
				<option value="MO">MO</option>
				<option value="MT">MT</option>
				<option value="NE">NE</option>
				<option value="NV">NV</option>
				<option value="NH">NH</option>
				<option value="NJ">NJ</option>
				<option value="NM">NM</option>
				<option value="NY">NY</option>
				<option value="NC">NC</option>
				<option value="ND">ND</option>
				<option value="OH">OH</option>
				<option value="OK">OK</option>
				<option value="OR">OR</option>
				<option value="PA">PA</option>
				<option value="RI">RI</option>
				<option value="SC">SC</option>
				<option value="SD">SD</option>
				<option value="TN">TN</option>
				<option value="TX">TX</option>
				<option value="UT">UT</option>
				<option value="VT">VT</option>
				<option value="VA">VA</option>
				<option value="WA">WA</option>
				<option value="WV">WV</option>
				<option value="WI">WI</option>
				<option value="WY">WY</option>
				</select> <input type="text" name="user_zip" size="15" maxlength="10" /></td></tr>
			<tr><td>Country</td><td><select name="user_country">
<option value="USA">USA</option>
<option value="Afghanistan">Afghanistan</option>
<option value="Albania">Albania</option>
<option value="Algeria">Algeria</option>
<option value="Andorra">Andorra</option>
<option value="Angola">Angola</option>
<option value="Antigua & Barbuda">Antigua & Barbuda</option>
<option value="Argentina">Argentina</option>
<option value="Armenia">Armenia</option>
<option value="Australia">Australia</option>
<option value="Austria">Austria</option>
<option value="Azerbaijan">Azerbaijan</option>
<option value="Bahamas">Bahamas</option>
<option value="Bahrain">Bahrain</option>
<option value="Bangladesh">Bangladesh</option>
<option value="Barbados">Barbados</option>
<option value="Belarus">Belarus</option>
<option value="Belgium">Belgium</option>
<option value="Belize">Belize</option>
<option value="Benin">Benin</option>
<option value="Bhutan">Bhutan</option>
<option value="Bolivia">Bolivia</option>
<option value="Bosnia & Herzegovina">Bosnia & Herzegovina</option>
<option value="Botswana">Botswana</option>
<option value="Brazil">Brazil</option>
<option value="Brunei">Brunei</option>
<option value="Bulgaria">Bulgaria</option>
<option value="Burkina Faso">Burkina Faso</option>
<option value="Burundi">Burundi</option>
<option value="Cambodia">Cambodia</option>
<option value="Cameroon">Cameroon</option>
<option value="Canada">Canada</option>
<option value="Cape Verde">Cape Verde</option>
<option value="Central African Republic">Central African Republic</option>
<option value="Chad">Chad</option>
<option value="Chile">Chile</option>
<option value="China">China</option>
<option value="Colombia">Colombia</option>
<option value="Comoros">Comoros</option>
<option value="Congo">Congo</option>
<option value="Congo Democratic Republic of">Congo Democratic Republic of</option>
<option value="Costa Rica">Costa Rica</option>
<option value="Cote d\'Ivoire">Cote d\'Ivoire</option>
<option value="Croatia">Croatia</option>
<option value="Cuba">Cuba</option>
<option value="Cyprus">Cyprus</option>
<option value="Czech Republic">Czech Republic</option>
<option value="Denmark">Denmark</option>
<option value="Djibouti">Djibouti</option>
<option value="Dominica">Dominica</option>
<option value="Dominican Republic">Dominican Republic</option>
<option value="Ecuador">Ecuador</option>
<option value="East Timor">East Timor</option>
<option value="Egypt">Egypt</option>
<option value="El Salvador">El Salvador</option>
<option value="Equatorial Guinea">Equatorial Guinea</option>
<option value="Eritrea">Eritrea</option>
<option value="Estonia">Estonia</option>
<option value="Ethiopia">Ethiopia</option>
<option value="Fiji">Fiji</option>
<option value="Finland">Finland</option>
<option value="France">France</option>
<option value="Gabon">Gabon</option>
<option value="Gambia">Gambia</option>
<option value="Georgia">Georgia</option>
<option value="Germany">Germany</option>
<option value="Ghana">Ghana</option>
<option value="Greece">Greece</option>
<option value="Grenada">Grenada</option>
<option value="Guatemala">Guatemala</option>
<option value="Guinea">Guinea</option>
<option value="Guinea-Bissau">Guinea-Bissau</option>
<option value="Guyana">Guyana</option>
<option value="Haiti">Haiti</option>
<option value="Honduras">Honduras</option>
<option value="Hungary">Hungary</option>
<option value="Iceland">Iceland</option>
<option value="India">India</option>
<option value="Indonesia">Indonesia</option>
<option value="Iran">Iran</option>
<option value="Iraq">Iraq</option>
<option value="Ireland">Ireland</option>
<option value="Israel">Israel</option>
<option value="Italy">Italy</option>
<option value="Jamaica">Jamaica</option>
<option value="Japan">Japan</option>
<option value="Jordan">Jordan</option>
<option value="Kazakhstan">Kazakhstan</option>
<option value="Kenya">Kenya</option>
<option value="Kiribati">Kiribati</option>
<option value="Korea North">Korea North</option>
<option value="Korea South">Korea South</option>
<option value="Kosovo">Kosovo</option>
<option value="Kuwait">Kuwait</option>
<option value="Kyrgyzstan">Kyrgyzstan</option>
<option value="Laos">Laos</option>
<option value="Latvia">Latvia</option>
<option value="Lebanon">Lebanon</option>
<option value="Lesotho">Lesotho</option>
<option value="Liberia">Liberia</option>
<option value="Libya">Libya</option>
<option value="Liechtenstein">Liechtenstein</option>
<option value="Lithuania">Lithuania</option>
<option value="Luxembourg">Luxembourg</option>
<option value="Macedonia">Macedonia</option>
<option value="Madagascar">Madagascar</option>
<option value="Malawi">Malawi</option>
<option value="Malaysia">Malaysia</option>
<option value="Maldives">Maldives</option>
<option value="Mali">Mali</option>
<option value="Malta">Malta</option>
<option value="Marshall Islands">Marshall Islands</option>
<option value="Mauritania">Mauritania</option>
<option value="Mauritius">Mauritius</option>
<option value="Mexico">Mexico</option>
<option value="Micronesia">Micronesia</option>
<option value="Moldova">Moldova</option>
<option value="Monaco">Monaco</option>
<option value="Mongolia">Mongolia</option>
<option value="Montenegro">Montenegro</option>
<option value="Morocco">Morocco</option>
<option value="Mozambique">Mozambique</option>
<option value="Myanmar (Burma)">Myanmar (Burma)</option>
<option value="Namibia">Namibia</option>
<option value="Nauru">Nauru</option>
<option value="Nepal">Nepal</option>
<option value="The Netherlands">The Netherlands</option>
<option value="New Zealand">New Zealand</option>
<option value="Nicaragua">Nicaragua</option>
<option value="Niger">Niger</option>
<option value="Nigeria">Nigeria</option>
<option value="Norway">Norway</option>
<option value="Oman">Oman</option>
<option value="Pakistan">Pakistan</option>
<option value="Palau">Palau</option>
<option value="Palestinian State">Palestinian State</option>
<option value="Panama">Panama</option>
<option value="Papua New Guinea">Papua New Guinea</option>
<option value="Paraguay">Paraguay</option>
<option value="Peru">Peru</option>
<option value="The Philippines">The Philippines</option>
<option value="Poland">Poland</option>
<option value="Portugal">Portugal</option>
<option value="Qatar">Qatar</option>
<option value="Romania">Romania</option>
<option value="Russia">Russia</option>
<option value="Rwanda">Rwanda</option>
<option value="St. Kitts & Nevis">St. Kitts & Nevis</option>
<option value="St. Lucia">St. Lucia</option>
<option value="St. Vincent & The Grenadines">St. Vincent & The Grenadines</option>
<option value="Samoa">Samoa</option>
<option value="San Marino">San Marino</option>
<option value="Sao Tome & Principe">Sao Tome & Principe</option>
<option value="Saudi Arabia">Saudi Arabia</option>
<option value="Senegal">Senegal</option>
<option value="Serbia">Serbia</option>
<option value="Seychelles">Seychelles</option>
<option value="Sierra Leone">Sierra Leone</option>
<option value="Singapore">Singapore</option>
<option value="Slovakia">Slovakia</option>
<option value="Slovenia">Slovenia</option>
<option value="Solomon Islands">Solomon Islands</option>
<option value="Somalia">Somalia</option>
<option value="South Africa">South Africa</option>
<option value="South Sudan">South Sudan</option>
<option value="Spain">Spain</option>
<option value="Sri Lanka">Sri Lanka</option>
<option value="Sudan">Sudan</option>
<option value="Suriname">Suriname</option>
<option value="Swaziland">Swaziland</option>
<option value="Sweden">Sweden</option>
<option value="Switzerland">Switzerland</option>
<option value="Syria">Syria</option>
<option value="Taiwan">Taiwan</option>
<option value="Tajikistan">Tajikistan</option>
<option value="Tanzania">Tanzania</option>
<option value="Thailand">Thailand</option>
<option value="Togo">Togo</option>
<option value="Tonga">Tonga</option>
<option value="Trinidad & Tobago">Trinidad & Tobago</option>
<option value="Tunisia">Tunisia</option>
<option value="Turkey">Turkey</option>
<option value="Turkmenistan">Turkmenistan</option>
<option value="Tuvalu">Tuvalu</option>
<option value="Uganda">Uganda</option>
<option value="Ukraine">Ukraine</option>
<option value="United Arab Emirates">United Arab Emirates</option>
<option value="United Kingdom">United Kingdom</option>
<option value="Uruguay">Uruguay</option>
<option value="Uzbekistan">Uzbekistan</option>
<option value="Vanuatu">Vanuatu</option>
<option value="Vatican City">Vatican City</option>
<option value="Venezuela">Venezuela</option>
<option value="Vietnam">Vietnam</option>
<option value="Yemen">Yemen</option>
<option value="Zambia">Zambia</option>
<option value="Zimbabwe">Zimbabwe</option>
</select></td></tr>
			</table>
			<input type="submit" id="regbutton" name="action" value="Submit Registration"></form>';
		}
		if ($_SESSION["loggedinuser"] != '')
		{
			$whitepapers = false;
			if (strpos($content, "White Papers") !== false) 
			{
				$body = '<h2>White Papers</h2>
				<p>Welcome to PFC White Papers, ' . $_SESSION["loggedinuser"] . '.</p>
				<p><small><small><strong>Jump to</strong> ';
				$categories = array("Whitepaper");
				$whitepapers = true;
				$query = 'select * from '. $wpdb->prefix . 'pfctraining_courses where type="Whitepaper" order by number';
				$data = $wpdb->get_results($query, ARRAY_A);
                foreach($data as $row)
				{
					$body = $body . " | <a href='#whitepaper-" . $row["id"] . "'>" . $row["title"] . "</a>";
				}
				$body .= "</small></small></p>";
			}
			else
			{
				$body = '<h2>Online Training Courses</h2>
				<p>Welcome to PFC Online Training, ' . $_SESSION["loggedinuser"] . '.</p>
				<p><small><small><strong>Jump to</strong><br /> ';
				$categories = array("Basic", "Advanced", "Elective");
				foreach ($categories as $category)
				{
					$body .= $category;
					$query = 'select * from '. $wpdb->prefix . 'pfctraining_courses where type="' . $category . '" order by number';
					$data = $wpdb->get_results($query, ARRAY_A);
					foreach($data as $row)
					{
						$body .= " | <a href='#course-" . $row["id"] . "'>" . $row["title"] . "</a>";
					}
					$body .= "<br />";
				}
				$body .= "</small></small></p>";
			}
			foreach ($categories as $category)
			{
				if (!$whitepapers)
				{
					$body = $body . '<h3 style="border-bottom: 1px dotted black;">' . $category . ' Courses</h3>';
				}
				$query = 'select * from '. $wpdb->prefix . 'pfctraining_courses where type="' . $category . '" order by number';
				$data = $wpdb->get_results($query, ARRAY_A);
				if (!$data) {
					$message  = 'Invalid query: ' . mysql_error() . "\n";
					$message .= 'Whole query: ' . $query;
					die($message);
				}
				foreach($data as $row)
				{
					$coursecounter = '/wp-content/plugins/pfctraining/coursecounter.php?userid='
						. $_SESSION["loggedinuserid"]
						. '&courseid='
						. $row["id"];

					if ($whitepapers)
					{
						$body = $body . "<a id='whitepaper-" . $row["id"] . "' href='" . $coursecounter . "&mediatype=WhitePaper&mediafile=" . $row["pdflink"] . "' target='_blank'><img src='" . $row["imagelink"] . "' style='float: left; border: 0px;' /></a><h3>" . $row["title"] . "</h3><p>" . $row["description"] . "</p><br style='clear: both;' />";
					}
					else
					{
						$body = $body . "<a id='course-" . $row["id"] . "'> <h3>" . $row["number"] . " - " . $row["title"] .  "</h3></a>" .
						"<p>" . $row["description"] . "<br/>";
						if (strlen($row["audiolink"]) > 0)
						{
							$body = $body . "<a href='" . $coursecounter . "&mediatype=MP3&mediafile=" . $row["audiolink"] . "' target='_blank'>Download
			English audio recording for course " .
							$row["number"] . "</a> <em>(" .
							$row["length"] . " minutes)</em><br />";
						}
						if (strlen($row["pdflink"]) > 0)
						{
							if ($whitepapers)
							{
								$mediatype = "White Paper";
							}
							else
							{
								$mediatype = "PDF";
							}
							$body = $body . "<a href='" . $coursecounter . "&mediatype=" . $mediatype . "&mediafile=" . $row["pdflink"] . "' target='_blank'>Download
			course manual for course " .
							$row["number"] . "</a> <em>(PDF)</em><br />";
						}
						if (strlen($row["testlink"]) > 0)
						{
							$body = $body . "<a href='" . $coursecounter . "&mediatype=Test&mediafile=" . $row["testlink"] . "' target='_blank'>Take test for credit for course " .
							$row["number"] . "</a> <br />";
						}
						$body = $body . "</p>";
					}
				}
			}
		}
		else if ($_POST["action"] == "")
		{
			$preserve = true;
			$body = $loginform;
		}
		if ($preserve)
		{
			$content = str_ireplace("[pfctraining-login]", $body, $content);
		}
		else
		{
			$content = $body;
		}
	}

	return $content;
}

function pfctrain_renderadminpage()
{
	global $wpdb;
	$wpdb->show_errors();
	
	?>
<form method="post">
<div class="wrap">
<h2>PFC Online Training Administration</h2>
<h3>Reports</h3>
<h4>Registered user report</h4>
<p><a href="/wp-content/plugins/pfctraining/report-users.php">All registered users</a></p>
<h4>Courses taken report</h4>
<p><a href="/wp-content/plugins/pfctraining/report-courses.php?last30=1">Courses taken in last 30 days</a></p>
<p><a href="/wp-content/plugins/pfctraining/report-courses.php">All courses taken</a></p>
<h3>User information</h3>
<p>There are currently <?php
	$query = 'select count(*) as users from '. $wpdb->prefix . 'pfctraining_users';
	$row = $wpdb->get_row($query, ARRAY_A);
	if (!$row) {
	    $message  = 'Invalid query: ' . mysql_error() . "\n";
	    $message .= 'Whole query: ' . $query;
	    die($message);
	}
	echo $row["users"] . " registered online training users, who have taken courses ";

	$query = 'select count(*) as taken from '. $wpdb->prefix . 'pfctraining_coursestaken';
	$row = $wpdb->get_row($query, ARRAY_A);
	if (!$row) {
	    $message  = 'Invalid query: ' . mysql_error() . "\n";
	    $message .= 'Whole query: ' . $query;
	    die($message);
	}
	echo $row["taken"] . " times";
?></p>
<h3>Add a course</h3>
<p>
<?php
if ($_POST["form-action"] == "reset")
{
	pfctrain_install();
}
if ($_POST["form-action"] == "add")
{
	$wpdb->insert($wpdb->prefix . 'pfctraining_courses', 
	array ('number' => $wpdb->escape($_POST["course_number"]),
	'length' => $wpdb->escape($_POST["course_length"]),
	'type' => $wpdb->escape($_POST["course_type"]),
	'title' => $wpdb->escape($_POST["course_title"]),
	'description' => $wpdb->escape($_POST["course_description"]),
	'audiolink' => $wpdb->escape($_POST["course_audiolink"]),
	'pdflink' => $wpdb->escape($_POST["course_pdflink"]),
	'testlink' => $wpdb->escape($_POST["course_testlink"])));
	echo "Course " . $wpdb->escape($_POST['course_title']) . " added. ";
}
if ($_POST["form-action"] == "execute")
{
	$query = str_replace("\\'", "'", $_POST["sql"]);
	echo '<code>' . $query . '</code>';
	$data = mysql_query($query);
	if (!$data) {
		$message  = 'Invalid query: ' . mysql_error() . "\n";
		$message .= 'Whole query: ' . $query;
		die($message);
	}
}

$query = 'select count(*) as courses from '. $wpdb->prefix . 'pfctraining_courses';
$row = $wpdb->get_row($query, ARRAY_A);
if (!$row) {
	$message  = 'Invalid query: ' . mysql_error() . "\n";
	$message .= 'Whole query: ' . $query;
	die($message);
}
echo "There are " . $row["courses"] . " courses in the database.";
?>
</p>
<table>
<tr>
<td>
Course number
</td>
<td>
<input type="text" name="course_number" size="8" />
</td>
</tr>
<tr>
<td>
Course type
</td>
<td>
<select name="course_type">
	<option value="basic">Basic</option>
	<option value="advanced">Advanced</option>
	<option value="elective">Elective</option>
</select>
</td>
</tr>
<tr>
<td>
Course length
</td>
<td>
<input type="text" name="course_length" size="4" /> minutes
</td>
</tr>
<tr>
<td>
Course title
</td>
<td>
<input type="text" name="course_title" size="40" />
</td>
</tr>
<tr>
<td valign="top">
Course description
</td>
<td>
<textarea name="course_description" rows="5" cols="40"></textarea>
</td>
</tr>
<tr>
<td valign="top">
Audio link
</td>
<td>
<input type="text" name="course_audiolink" value="" size="60" /> (optional)
</td>
</tr>
<tr>
<td valign="top">
PDF link
</td>
<td>
<input type="text" name="course_pdflink" value="" size="60" /> (optional)
</td>
</tr>
<tr>
<td valign="top">
Exam link
</td>
<td>
<input type="text" name="course_testlink" value="" size="60" /> (optional)
</td>
</tr>
</table>
<input type="hidden" name="form-action" value="add" />
<input type="submit" class="button-primary" value="Add Course" />
</div>
</form>
<h3>Advanced</h3>
<p>Use with caution.</p>
<form method="post">
<input type="hidden" name="form-action" value="reset" />
<input type="submit" class="button-primary" value="Repair database tables" />
</form>
<form method="post">
<input type="hidden" name="form-action" value="execute" />
<textarea name="sql" rows="5" cols="40"><?php echo $wpdb->prefix ?></textarea><br />
<input type="submit" class="button-primary" value="Execute statement" />
</form>


	<?
}

function pfctrain_registeroptions() 
{
	add_options_page('PFC Online Training', 'PFC Online Training', 9, basename(__FILE__), 'pfctrain_renderadminpage');
}

add_filter('the_content', 'pfctrain_replaceshortcodes');
register_activation_hook(__FILE__,'pfctrain_install');
add_action('admin_menu', 'pfctrain_registeroptions');

?>
