<?php

include_once "_config.php" ;
include_once SEEDCore."SEEDCore.php" ;
include_once SEEDROOT."Keyframe/KeyframeForm.php" ;
include_once SEEDROOT."Keyframe/KeyframeDB.php" ;
require_once "database.php" ;

var_dump($_REQUEST);
$kfdb->SetDebug(1);

$client_fields = array("client_name","parents_name","address","city","postal_code","dob","phone_number","email","family_doc","paediatrician","slp","psychologist","referal","background_info");
$pro_fields = array("pro_name","pro_role","address","city","postal_code","phone_number","fax_number","email");

if( !isset($dirBootstrap) ) { $dirBootstrap = "./bootstrap3/"; }
if( !isset($dirJQuery) )    { $dirJQuery =    "./jquery/"; }

$s =
"<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<link rel='stylesheet' type='text/css' href='".$dirBootstrap."dist/css/bootstrap.min.css'></link>
<script src='".$dirJQuery."jquery-1.11.0.min.js'></script>
<script src='".$dirBootstrap."dist/js/bootstrap.min.js'></script>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src='//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js'></script>
<script src='//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js'></script>
<![endif]-->

<style>
.otButton { border:1px solid #aaa; width:80%; text-align:center; padding: 20px 0px;};
</style>
<script>
function createCircle(toStyle, radius, color, textColor) {
	toStyle.style.display = 'inline-block';
	toStyle.style.height = 2 * radius + 'px';
    toStyle.style.marginBottom = '20px';
    toStyle.style.marginLeft = '20px';
	toStyle.style.width = toStyle.style.height;
	toStyle.style.lineHeight = toStyle.style.height;
	toStyle.style.textAlign = 'center';
	toStyle.style.color = textColor;
	toStyle.style.borderStyle = 'inset outset outset inset';
	toStyle.style.borderColor = color;
	toStyle.style.backgroundColor = color;
	toStyle.style.borderRadius = radius + 'px';
	return true;
}
function run() {
    var x = document.querySelectorAll('a.toCircle');
    for(var y = 0; y < x.length; y++) {
	   var classes = x[y].classList;
	   for(var loop in classes) {
		  if (classes.item(loop).search(/format-\d+-#?[\d\w]+-#?[\d\w]+/) !== -1) {
			 var parse = classes.item(loop).split('-');
			 createCircle(x[y], parse[1], parse[2], parse[3]);
		  }
	   }
    }
}
</script>
</head>
<body>";


$screen = SEEDInput_Str( 'screen' );

if( $screen == 'admin' ) {
    $s .= drawAdmin();
} else if( substr( $screen, 0, 9 ) == "therapist" ) {
    $s .= drawTherapist( $screen );
} else {
    $s .= drawHome();
}

echo $s
    ."<script> run(); </script></body></html>";



function drawHome()
{
    $s = "";


    $s = "<h2>Home</h2>";

    $s .= "<a href='?screen=therapist' class='toCircle format-100-#b3f0ff-blue'>Therapist</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='?screen=admin' class='toCircle format-100-red-black'>Admin</a>";

    return( $s );
}

function drawTherapist( $screen )
{
    global $kfdb;

    $s = "<h2>Therapist</h2>";

    switch( $screen ) {
        case "therapist":
        default:
            $s .= "<p>What would you like to do?</p>"
                 ."<div class='container-fluid'>"
                     ."<div class='row'>"
                         ."<div class='col-md-3'>"
                             ."<a href='?screen=home' class='toCircle format-100-#b3f0ff-blue'>Home</a>"
                         ."</div>"
                         ."<div class='col-md-3'>"
                             ."<a href='?screen=therapist-materials' class='toCircle format-100-#99ff99-blue'>Print Materials</a>"
                         ."</div>"
                         ."<div class='col-md-3'>"
                             ."<a href='?screen=therapist-formscharts' class='toCircle format-100-#b3f0ff-blue'>Print Forms for Charts</a>"
                         ."</div>"
                         ."<div class='col-md-3'>"
                             ."<a href='?screen=therapist-linedpapers' class='toCircle format-100-#99ff99-blue'>Print Different Lined Papers</a>"
                         ."</div>"
                     ."</div>"
                     ."<div class='row'>"
                         ."<div class='col-md-3'>"
                             ."<a href='?screen=therapist-entercharts' class='toCircle format-100-#99ff99-blue'>Enter Charts</a>"
                         ."</div>"
                         ."<div class='col-md-3'>"
                             ."<a href='?screen=therapist-clientRx' class='toCircle format-100-#b3f0ff-blue'>Print Client Rx Activities</a>"
                         ."</div>"
                         ."<div class='col-md-3'>"
                             ."<a href='?screen=therapist-ideas' class='toCircle format-100-#99ff99-blue'>Get Ideas</a>"
                         ."</div>"
                         ."<div class='col-md-3'>"
                             ."<a href='?screen=therapist-downloadcustommaterials' class='toCircle format-100-#99ff99-blue'>Download Materials</a>"
                         ."</div>"
                     ."</div>"
                     ."<div class='row'>"
                         ."<div class='col-md-3'>"
                             ."<a href='?screen=therapist-team' class='toCircle format-100-#b3f0ff-blue'>Meet the Team</a>"
                         ."</div>"
                         ."<div class='col-md-3'>"
                             ."<a href='?screen=therapist-submitresources' class='toCircle format-100-#99ff99-blue'>Submit Resources to Share</a>"
                         ."</div>"
                         ."<div class='col-md-3'>"
                             ."<a href='?screen=therapist-clientlist' class='toCircle format-100-#99ff99-blue'>Clients and Providers</a>"
                         ."</div>"
                     ."</div>"
                 ."</div>";
            break;
        case "therapist-materials":
            $s .= "PRINT MATERIALS";
            break;
        case "therapist-formscharts":
            $s .= "PRINT FORMS FOR CHARTS";
            break;
        case "therapist-linedpapers":
            $s .= "PRINT DIFFERENT LINED PAPERS";
            break;
        case "therapist-entercharts":
            $s .= "ENTER CHARTS";
            break;
        case "therapist-clientRx":
            $s .= "PRINT CLIENT Rx ACTIVITIES";
            break;
        case "therapist-ideas":
            $s .= "GET IDEAS";
            break;
        case "therapist-downloadcustommaterials":
            $s .= "DOWNLOAD AND CUSTOMIZE MARKETABLE MATERIALS";
            break;
        case "therapist-team":
            $s .= "MEET THE TEAM";
            break;
        case "therapist-submitresources":
            $s .= "SUBMIT RESOURCES";
            break;
        case "therapist-clientlist":
            $s .= drawClientList( $kfdb );
            break;
    }

    return( $s );
}

function drawAdmin()
{
    $s = "";

    $s = "<h2>Admin</h2>";

    $s .= "<a href='?screen=home' class='toCircle format-100-#99ff99-blue'>Home</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='?screen=therapist' class='toCircle format-100-#99ff99-blue'>Therapist</a>";

    return( $s );
}

function drawClientList( KeyframeDatabase $kfdb )
{
    global $client_fields, $oClientsDB, $pro_fields, $oProsDB, $oClients_ProsDB;

    $s = "";

    $oFormClient = new KeyframeForm( $oClientsDB->KFRel(), "Plain" );

    $client_key = SEEDInput_Int( 'client_key' );
    $pro_key = SEEDInput_Int( 'pro_key' );

    if( $client_key ) {
        $kfrClient = $oClientsDB->GetClient( $client_key );
        $oFormClient->SetKFR( $kfrClient );
    }

    // Put this before the GetClients call so the changes are shown in the list
    if( ($cmd = SEEDInput_Str('cmd')) == "update_client" ) {
        $kfr = $oClientsDB->GetClient( $client_key );
        $kfr->SetValue( 'parents_separate', SEEDInput_Str("parents_separate") == "on" );
        foreach( $client_fields as $field ) {
            $kfr->SetValue( $field, SEEDInput_Str($field) );
        }
        $kfr->PutDBRow();
    }
    elseif( ($cmd = SEEDInput_Str('cmd')) == "update_pro" ) {
        $kfr = $oProsDB->GetPro( $pro_key );
        foreach( $pro_fields as $field ) {
            $kfr->SetValue( $field, SEEDInput_Str($field) );
        }
        $kfr->PutDBRow();
    }

    $clientPros = array();
    $proClients = array();
    if( $client_key ) {
        // A client has been clicked. Who are their pros?
        $myPros = $oClients_ProsDB->KFRel()->GetRecordSetRA("Clients._key='$client_key'" );
    }
    if( $pro_key ) {
        // A client has been clicked. Who are their pros?
        $myClients = $oClients_ProsDB->KFRel()->GetRecordSetRA("Pros._key='$pro_key'" );
    }

    $raClients = $oClientsDB->KFRel()->GetRecordSetRA("");
    $raPros = $oProsDB->KFRel()->GetRecordSetRA("");

    $s .= "<div class='container-fluid'><div class='row'>"
         ."<div class='col-md-6'>"
             ."<h3>Clients</h3>"
             .SEEDCore_ArrayExpandRows( $raClients, "<div style='padding:5px;'><a href='?client_key=[[_key]]&screen=therapist-clientlist'>[[client_name]]</a></div>" )
             .($client_key ? drawClientForm( $oFormClient, $kfdb, $raClients, $myPros, $client_key) : "")
         ."</div>"
         ."<div class='col-md-6'>"
             ."<h3>Providers</h3>"
             .SEEDCore_ArrayExpandRows( $raPros, "<div style='padding:5px;'><a href='?pro_key=[[_key]]&screen=therapist-clientlist'>[[pro_name]]</a> is a [[pro_role]]</div>" )
             .($pro_key ? drawProForm( $kfdb, $raPros, $pro_key) : "")
         ."</div>"
         ."</div></div>";

    return( $s );
}


function drawClientForm( $oFormClient, $kfdb, $raClients, $myPros, $client_key )
{
    $s = "";

    // The user clicked on a client name so show their form
    foreach( $raClients as $ra ) {
        if( $ra['_key'] == $client_key ) {
            // Dad says: don't bother putting doctor, paed, slp names in this form. Instead we'll make a "connect-the-professionals" map
            //    between the clients and professionals tables.
            $sPros = "<div style='padding:10px;border:1px solid #888'>"
                    .SEEDCore_ArrayExpandRows( $myPros, "[[Pros_pro_name]] is my [[Pros_pro_role]]" )
                    ."</div>";

            //TODO Joe: make this form into a nice bootstrappy table so the input controls are aligned vertically
            $sForm =
                  "<form>"
                 ."<input type='hidden' name='cmd' value='update_client'/>"
                 ."<input type='hidden' name='client_key' value='$client_key'/>"
                 ."<input type='hidden' name='screen' value='therapist-clientlist'/"
                 ."<p>Client # $client_key</p>"
                 ."<div class='container-fluid'>"
                 ."<div class='row'>"
                     ."<div class='col-md-4'>Name</div>"
                     ."<div class='col-md-8'>".$oFormClient->Text('client_name',"",array("attrs"=>"required maxlength='200'"))."</div>"
                 ."</div>"
                 ."<div class='row'>"
                     ."<div class='col-md-4'>Parents Name</div>"
                     ."<div class='col-md-8'>".$oFormClient->Text('parents_name',"",array("attrs"=>"maxlength='200'"))."</div>"
                 ."</div>"
                 ."</div>" // div container

                 ."<p>Parents Seperate <input type='checkbox' name='parents_separate' maxlength='200' ".($ra['parents_separate']?"checked":"")."/></p>"
                     ."<p>Address ".$oFormClient->Text('address',"",array("attrs"=>"maxlength='200'"))."</p>"
                         ."<p>City ".$oFormClient->Text('city',"",array("attrs"=>"maxlength='200'"))."</p>"
                             ."<p>Postal Code ".$oFormClient->Text('Postal Code',"",array("attrs"=>"maxlength='200'"))."</p>"
                 ."<p>Date Of Birth <input type='date' name='dob' value='".htmlspecialchars($ra['dob'])."'/></p>"
                 ."<p>Phone Number <input type='text' name='phone_number' maxlength='200' value='".htmlspecialchars($ra['phone_number'])."'/></p>"
                 ."<p>Email <input type='email' name='email' maxlength='200' value='".htmlspecialchars($ra['email'])."'/></p>"
                 ."<p><input type='submit' value='Save'/></p>"
                 ."</form>";


            $s .= "<div class='container-fluid' style='border:1px solid #aaa;padding:20px;margin:20px'>"
                 ."<div class='row'>"
                     ."<div class='col-md-9'>".$sForm."</div>"
                     ."<div class='col-md-3'>".$sPros."</div>"
                 ."</div>"
                 ."</div>";
        }
    }
    return( $s );
}

function drawProForm( $kfdb, $raPros, $pro_key )
{
    $s = "";

    // The user clicked on a professionals name so show their form
    foreach( $raPros as $ra ) {
        if( $ra['_key'] == $pro_key ) {

            //TODO Joe: make this form into a nice bootstrappy table so the input controls are aligned vertically
            $s .= "<div style='border:1px solid #aaa;padding:20px;margin:20px'>"
                ."<form>"
                ."<input type='hidden' name='cmd' value='update_pro'/>"
                ."<input type='hidden' name='pro_key' value='$pro_key'/>"
                ."<input type='hidden' name='screen' value='therapist-clientlist'/"
                ."<p>Professional # $pro_key</p>"
                ."<p>Name <input type='text' name='pro_name' required maxlength='200' value='".htmlspecialchars($ra['pro_name'])."'/></p>"
                ."<p>Address <input type='text' name='address' maxlength='200' value='".htmlspecialchars($ra['address'])."'/></p>"
                ."<p>City <input type='text' name='city' maxlength='200' value='".htmlspecialchars($ra['city'])."'/></p>"
                ."<p>Postal Code <input type='text' name='postal_code' maxlength='200' value='".htmlspecialchars($ra['postal_code'])."'/></p>"
                ."<p>Phone Number <input type='text' name='phone_number' maxlength='200' value='".htmlspecialchars($ra['phone_number'])."'/></p>"
                ."<p>Fax Number <input type='text' name='fax_number' maxlength='200' value='".htmlspecialchars($ra['fax_number'])."'/></p>"
                ."<p>Email <input type='email' name='email' maxlength='200' value='".htmlspecialchars($ra['email'])."'/></p>"
                ."<p>Role <input type='text' name='pro_role' maxlength='200' value='".htmlspecialchars($ra['pro_role'])."'/></p>"
                ."<p><input type='submit' value='Save'/></p>"
                ."</form>"
                ."</div>";
        }
    }
    return( $s );
}

?>
