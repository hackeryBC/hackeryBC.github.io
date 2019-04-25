<?php
# uses pear to avoid having to write a whole injection-proofing framework
include('Mail.php');

# request.php replacement for Python `request' CGI on thehackery.ca 
# Michael Fincham <michael@hotplate.co.nz> 2014-07-07

###
### Settings you might want to adjust
###

date_default_timezone_set('America/Los_Angeles');

$headers['From']    = 'request@thehackery.ca';
$headers['To']      = 'recycling@thehackery.ca';

if ($_POST['company'] and strlen($_POST['company']) > 0) {
    $headers['Subject'] = 'New electronics pick up request from '. $_POST['company'] ;
} else {
    $headers['Subject'] = 'New electronics pick up request';
}

$body = "A request for electronics pick up has been made through the website.\n\n";

# the URL that will be redirected back to, if enabled
$form_url = "http://thehackery.ca/request.html";

# how long in seconds to wait before redirect back to the form after submission. 0 = do not redirect automatically.
$refresh_time = 0;

$recipients = array(
    # Copied from original `request' file
    "iamturnip@gmail.com",
    "recycling@thehackery.ca"
);

# If there is a field called "email" it will be copied in to the Reply-To header of the sent mail, all other fields get no special treatment
$form_fields = array(
    "company" => array('desc' => "Company name", 'field' => 'text'),
    "contact" => array('desc' => "Contact name", 'field' => 'text'),
    "phone" => array('desc' => "Contact phone number", 'field' => 'text'),
    "email" => array('desc' => "E-mail address", 'field' => 'email'), # 'email' field type gets copied in to the Reply-To header
    "address" => array('desc' => "Pick up address", 'field' => 'text'),
    "hours" => array('desc' => "Pick up hours", 'field' => 'text'),
    "time" => array('desc' => "Pick up date", 'field' => 'text'),
    "skid" => array('desc' => "Equipment is wrapped on skids", 'field' => 'text'),
    "parking" => array('desc' => "Off-street parking", 'field' => 'text'),
    "stairs" => array('desc' => "Needs to be carried down stairs", 'field' => 'text'),
    "ewaste" => array('desc' => "Description of items", 'field' => 'textarea') # 'textarea' field type gets multi-line display
);

$banned_ips = array(
    # Copied from original `request' file
    "188.143.232.111",
    "188.143.232.31"
);

# If this field is sent with a length > 0, the form will be discarded
$spam_canary_field = "comments";

###
### End of settings... the rest of the script delivers the e-mail the renders a page with the results.
###

# kinda unreliable check to see if the IP is banned
$remote_ip = $_SERVER['REMOTE_ADDR'];
if (in_array($remote_ip, $banned_ips)) {
    header("Location: " . $form_url);
    die();
}

# if the canary field has been submitted...
if ($_POST[$spam_canary_field] and strlen($_POST[$spam_canary_field]) > 0) {
    header("Location: " . $form_url);
    die();
}

# ... or if the form wasn't submitted, just give up and go back to the request page.
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: " . $form_url);
    die();
}

$body .= "At: " . strftime("%a %b %d %Y %H:%M") . "\n";
$body .= "From IP: " . $remote_ip . "\n\n";

# loop through the fields we got. no validation, but I doubt it'll be a problem.
foreach ($form_fields as $field => $field_properties) {
    if ($_POST[$field] and strlen($_POST[$field]) > 0) {
        if ($field_properties['field'] == 'email') { # e-mail field
            $headers['Reply-To'] = $_POST[$field];
            $body .= $field_properties['desc'] . ": " . $_POST[$field] . "\n";
        } elseif ($field_properties['field'] == 'textarea') { # big text field
            $body .= $field_properties['desc'] . ":\n\n" . $_POST[$field] . "\n\n";
        } else {
            $body .= $field_properties['desc'] . ": " . $_POST[$field] . "\n";
        }
    }
}

# if the submitter supplies an e-mail address, make it the reply-to header
if ($_POST['email'] and strlen($_POST['email']) > 0) {
}


try {
    $mail_object =& Mail::factory('mail');
    $mail_object->send($recipients, $headers, $body);
    $success = 1;
} catch (Exception $e) {
    $success = 0;
}

if ($refresh_time > 0) {
    header("refresh:" . $refresh_time . ";url=" . $form_url); 
}

?>
<?php echo '<?xml version="1.0" encoding="utf-8"?>' ?>
<!doctype html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-CA">
 <head>
  <title>The Hackery :: Computer Recycling Vancouver Pickup Form</title>
  
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="Description" content="The Hackery specializes in laptop and LCD monitor repair, software troubleshooting, recycling, and sales for Vancouver, BC. Home of Vancouver's one and only laptop and LCD scrap yard!" />
  <meta name="Keywords" content="hackery thehackery.ca laptop repair monitor LCD flat screen recycling Vancouver computer" />
  <meta name="Rating" content="General" />
  
  <link rel="stylesheet" type="text/css" href="styling/reset.css" media="all" />
  <link rel="stylesheet" type="text/css" href="styling/style.css" media="screen" />
  <link rel="icon" type="image/vnd.microsoft.icon" href="styling/favicon.ico" /> <!--standard-->
  <link rel="SHORTCUT ICON" href="styling/favicon.ico" /> <!--for MS IE-->
  <link rel="index" type="text/html" href="index.html" title="The Hackery" />
  <link rel="next" type="text/html" href="index.html" title="The Hackery" />
  <link rel="prev" type="text/html" href="recycling.html" title="Laptop and Monitor Recycling" />
 </head>
 <body>
  <div id="wrap">
   <div id="header">
    <h1><a href="index.html" title="thehackery.ca Home">The Hackery</a></h1>
    <dl id="menu">
     <dt><a href="index.html">Home</a></dt>
     <!-- <dt><a href="#">Sale</a></dt>-->
     <dt><a href="parts.html">Parts</a></dt>
     <dt><a href="repair.html">Repair</a></dt>
     <dt><a href="recycling.html">Recycling</a></dt>
     <dt class="selected"><a href="contact.html">Contact</a></dt>
    </dl>
   </div>
   <div id="content" class="clear">
    <h1>Electronics Pick Up Request Form</h1>
    <p>

<?php 

if ($success == 1) {

?>

Thanks, your pick up request has been submitted and a representative will be in touch shortly.

<?php 

} else {

?>

We were not able to submit your pick up request electronically. Please call us at 778-373-8295 or email us at <a href="mailto:recycling@thehackery.ca">recycling@thehackery.ca</a> to arrange a pick up.

<?php

}

?>

  </div>
  <div id="footer">
   <div>
    <ul>
     <li>304 Victoria Drive; Vancouver, BC V5L 4C7</li>
     <li>778-373-8295</li>
     <li>Tues&#8211;Sat, 11am&#8211;6pm</li>
     <td width="16%"><a href="http://facebook.com/thehackery" target="_blank"><img src="images/fbicon_20x20.gif" alt="facebook" width="20" height="20" border="0"></a></td>
                                        <td width="16%"><a href="http://twitter.com/thehackery" target="_blank"><img src="images/twittericon_20x20.gif" alt="twitter" width="20" height="20" border="0"></a></td>
                                        <td width="13%"><a href="http://www.youtube.com/user/thehackery" target="_blank"><img src="images/youtubeicon_20x20.gif" alt="YouTube" width="20" height="20" border="0"></a></td>
    </ul>
   </div>
  </div>
  
  <script type="text/javascript">
  var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
  document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
  try{
  var pageTracker = _gat._getTracker("UA-11607816-1");
  pageTracker._trackPageview();
  }catch(err){}
  </script>
 </body>
</html>
