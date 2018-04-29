<?php

include( SEEDROOT."seedlib/SEEDGoogleService.php" );

class Calendar
{
    private $oApp;
    private $sess;  // remove this, use oApp->sess instead

    function __construct( SEEDAppSessionAccount $oApp )
    {
        $this->oApp = $oApp;
        $this->sess = $oApp->sess;  // remove this
    }

    function DrawCalendar()
    {
        $s = "";

        $oGC = new CATS_GoogleCalendar();               // for appointments on the google calendar
        $oApptDB = new AppointmentsDB( $this->oApp );   // for appointments saved in cats_appointments

        /* Get a list of all the calendars that this user can see
         */
        list($raCalendars,$sCalendarIdPrimary) = $oGC->GetAllMyCalendars();

        /* Get the id of the calendar that we're currently looking at. If there isn't one, use the primary.
         */
        $calendarIdCurrent = $this->sess->SmartGPC( 'calendarIdCurrent' ) ?: $sCalendarIdPrimary;

        /* If the user has booked a free slot, store the booking
         */
        if( ($bookSlot = SEEDInput_Str("bookSlot")) && ($sSummary = SEEDInput_Str("bookingSumary")) ) {
            $oGC->BookSlot( $calendarIdCurrent, $bookSlot, $sSummary );
            echo("<head><meta http-equiv=\"refresh\" content=\"0; URL=".CATSDIR."\"></head><body><a href=".CATSDIR."\"\">Redirectn</a></body>");
            die();
        }

        if( false ) {   // when a new appointment is made via the new-appointment form use this code
/*
                $kfr = $oApptDB->KFRel()->CreateRecord();
                    $kfr->SetValue("google_event_id", $event->id);
                    $kfr->SetValue("start_time", substr($event->start->dateTime, 0, 19) );  // yyyy-mm-ddThh:mm:ss is 19 chars long; trim the timezone part
                    $kfr->PutDBRow();
*/
        }


        /* Show the list of calendars so we can choose which one to look at
         * The current calendar will be selected in the list.
         */
        $oForm = new SEEDCoreForm('Plain');

        $s .= "<form method='post'>"
             .$oForm->Select( 'calendarIdCurrent', $raCalendars, "Calendar",
                              array( 'selected' => $calendarIdCurrent, 'attrs' => "onchange='submit();'" ) )
             ."</form>";


        // Get the dates of the monday-sunday period that includes the current day.
        // Yes, php can do this and a lot of other cool natural-language dates.
        //
        // Note that "this monday" means the monday contained within the next 7 days, "last monday" gives a week ago if today is monday,
        // so "monday this week" is better than those
        $tMonThisWeek = strtotime('monday this week');

        if( !($tMon = SEEDInput_Int('tMon')) ) {
            $tMon = $tMonThisWeek;
        }
        $tSun = $tMon + (3600 * 24 * 7 ) - 60;    // Add seven days (in seconds) then subtract a minute. That's the end of next sunday.

        /* Get the google calendar events for the given week
         */
        $raEvents = $oGC->GetEvents( $calendarIdCurrent, $tMon, $tSun );

        /* Get the list of calendar events from Google
         */
        $sList = "";
        if( !count($raEvents) ) {
            $sList .= "No upcoming events found.";
        } else {
            $lastday = "";
            foreach( $raEvents as $event ) {
                /* Surround the events of each day in a <div class='day'> wrapper
                 */
                if( !($start = $event->start->date) ) {
                    $start = strtok( $event->start->dateTime, "T" );    // strtok returns string before T, or whole string if there is no T
                }
                if($start != $lastday){
                    if($lastday != ""){
                        $sList .= "</div>";
                    }
                    $sList .= "<div class='day'>";
                    $time = new DateTime($start);
                    $sList .= "<span class='dayname'>".$time->format("l F jS Y")."</span>";
                    $lastday = $start;
                }

                /* Non-admin users are only allowed to see Free slots and book them
                 */
                if( !$this->sess->CanAdmin('Calendar') ) {
                    // The current user is only allowed to see Free slots and book them
                    if( strtolower($event->getSummary()) != "free" )  continue;

                    $sList .= $this->drawEvent( $event, 'nonadmin', null );

                } else {
                    // Admin user: check this google event against our appointment list
                    $kfrAppt = $oApptDB->KFRel()->GetRecordFromDB("google_event_id = '".$event->id."'");

                    if( !$kfrAppt ) {
                        // NEW: this google event is not yet in cat_appointments; show the form to add the appointment
                        $eType = 'new';
                    } else {
                        // Compare the start time of the google event and the cats appointment.
                        // If they're the same, draw the normal appt. If they're different, show a notice.
                        $dGoogle = substr($event->start->dateTime, 0, 19);  // yyyy-mm-ddThh:mm:ss is 19 chars long; trim the timezone part
                        $dCats = $kfrAppt->Value('start_time');
                        if( (substr($dGoogle,0,strpos($dGoogle, "T"))." ".substr($dGoogle,strpos($dGoogle, "T")+1) == $dCats )) {
                            $eType = 'normal';
                        } else {
                            $eType = 'moved';
                        }
                    }

                    $sList .= $this->drawEvent( $event, $eType, $kfrAppt );
                }

            }
            if( $sList )  $sList .= "</div>";   // end the last <div class='day'>
        }

        $linkGoToThisWeek = ( $tMon != $tMonThisWeek ) ? "<a href='?tMon=$tMonThisWeek'> Back to the current week </a>" : "";
        $sCalendar = "<div class='row'>"
                        ."<div class='col-md-1'><a href='?tMon=".($tMon-3600*24*7)."'> &lt;- </a></div>"
                        ."<div class='col-md-8'><h3>Appointments from ".date('Y-m-d', $tMon)." to ".date('Y-m-d', $tSun)."</h3></div>"
                        ."<div class='col-md-2'>$linkGoToThisWeek</div>"
                        ."<div class='col-md-1'><a href='?tMon=".($tMon+3600*24*7)."'> -&gt; </a></div>"
                    ."</div>";
        $sCalendar .= $sList;


        /* Get the list of appointments known in CATS
         */
        $sAppts = "<h3>CATS appointments</h3>";
        $raAppts = $oApptDB->GetList( "eStatus in ('NEW','REVIEWED')" );
        foreach( $raAppts as $ra ) {
            $eventId = $ra['google_event_id'];
            $eStatus = $ra['eStatus'];
            $startTime = $ra['start_time'];
            $clientId = $ra['fk_clients'];

            // Now look through the $raEvents that you got from google and try to find the google event with the same event id.
            // If the date/time is different (someone changed it it google calendar), give a warning in $sAppts.
            // If the client is not known clientId==0, give a warning in $sAppts.
//this was just temporary; the CATS appointments will be built into the main calendar now
//            $sAppts .= "<div>$startTime : $clientId</div>";
        }

        //$s .= "<div class='row'><div class='col-md-6'>$sCalendar</div><div class='col-md-6'>$sAppts</div></div>";
        $s .= $sCalendar;

        $s .= "
    <style>
       span.appt-time,span.appt-summary {
	       font-family: 'Roboto', sans-serif;
        }
       .drop-arrow {
	       transition: all 0.2s ease;
	       width: 10px;
	       height: 10px;
	       display: inline;
	       transform: none;
        }
        .collapsed .drop-arrow {
	       transform: rotate(-90deg);
        }
        .appointment {
	       transition: all 0.2s ease;
	       overflow: hidden;
	       border: 1px dotted gray;
	       border-radius: 5px;
	       width: 105px;
	       padding: 2px;
	       background-color: #99ff99;
	       margin-top: 5px;
	       margin-bottom: 5px;
           box-sizing: content-box;
        }
        .collapsed .appointment {
	       height: 0;
	       border: none;
	       padding: 0;
	       margin: 0;
        }
        .day {
	       margin: 2px;
        }
    </style>
    <script>
        var x = document.createElement('img');
        x.src = 'https://cdn1.iconfinder.com/data/icons/pixel-perfect-at-16px-volume-2/16/5001-128.png';
        x.className = 'drop-arrow';
        var z = document.getElementsByClassName('day');
        for(y = 0; y < z.length; y++) {
	       var w = x.cloneNode();
	       z[y].insertBefore(w, z[y].firstChild);
	       w.onclick = rotateMe;
        }
        function rotateMe() {
	       this.parentElement.classList.toggle('collapsed');
        }
        function expand() {
	       var days = document.getElementsByClassName('day');
	       for (var loop = 0; loop < days.length; loop++) {
		   days[loop].classList.remove('collapsed');
	   }
    }
    function collapse() {
	   var days = document.getElementsByClassName('day');
	   for (var loop = 0; loop < days.length; loop++) {
	       days[loop].classList.add('collapsed');
	   }
    }
</script>";

        return( $s );
    }

    private function drawEvent( $event, $eType, KeyframeRecord $kfrAppt = null )
    /***************************************************************************
        eType:
            nonadmin = the user is only allowed to see Free slots and book them. This method is only called for Free slots.
            normal   = this event matches the appointment in cats_appointments
            moved    = this event is in cats_appointments but it has been moved to a different datetime.
            new      = this event is not stored in cats_appointments so show a form for adding it.
     */
    {
        $s = "";

        $admin = $eType != 'nonadmin';

        if(strtolower($event->getSummary()) != "free" && !$admin){
            return "";
        }

        $tz = "";
        if( !($start = $event->start->dateTime) ) {
            $start = $event->start->date;
        }
        elseif ($event->start->timeZone) {
            $tz = $event->start->timeZone;
        }
        else{
            $tz = substr($start, -6);
            $start = substr($start, 0,-6);
        }
        if( !$tz ) $tz = 'America/Toronto';
        $time = new DateTime($start, new DateTimeZone($tz));


        $classFree = strtolower($event->getSummary()) == "free" ? "free" : "busy";
        $sOnClick = strtolower($event->getSummary()) == "free" ? $this->bookable($event->id) : "";

        switch( $eType ) {
            case 'new':
                $sSpecial = "THIS APPOINTMENT IS NEW - PUT FORM HERE";
                break;
            case 'moved':
                $sSpecial = "NOTICE: THIS APPOINTMENT HAS MOVED - OK";
                break;
            default:
                $sSpecial = "";
                break;
        }

        $s .= "<div class='appointment $classFree' $sOnClick >"
             ."<span class='appt-time'>".$time->format("g:ia")."</span>"
             .($admin ? ("<span class='appt-summary'>".$event->getSummary()."</span>") : "")
             .$sSpecial
             ."</div>";

        return $s;
    }

    private function bookable($id){
        $s = " onclick=\"";
        $s .= "";
        $s .= "window.location='?bookSlot=$id&bookingSumary=";
        $s .= "' + prompt('Who is this appointment for?');";
        $s .= "\"";
        return $s;
    }
}


class CATS_GoogleCalendar
{
    var $service;

    function __construct()
    {
        $raGoogleParms = array(
                'application_name' => "Google Calendar for CATS",
                // If modifying these scopes, regenerate the credentials at ~/seed_config/calendar-php-quickstart.json
//                'scopes' => implode(' ', array( Google_Service_Calendar::CALENDAR_READONLY ) ),
                'scopes' => implode(' ', array( Google_Service_Calendar::CALENDAR ) ),
                // Downloaded from the Google API Console
                'client_secret_file' => CATS_CONFIG_DIR."google_client_secret.json",
                // Generated by getcreds.php
                'credentials_file' => CATS_CONFIG_DIR."calendar-php-quickstart.json",
        );

        $oG = new SEEDGoogleService( $raGoogleParms, false );
        $oG->GetClient();
        $this->service = new Google_Service_Calendar($oG->client);
    }


    function GetAllMyCalendars()
    {
        $raCalendars = array();
        $sCalendarIdPrimary = "";

        if( !$this->service ) goto done;

        $opts = array();
        // calendars are paged; pageToken is not specified on the first time through, then nextPageToken is specified as long as it exists
        while( ($calendarList = $this->service->calendarList->listCalendarList( $opts )) ) {
            foreach ($calendarList->getItems() as $calendarListEntry) {
                $raCalendars[$calendarListEntry->getSummary()] = $calendarListEntry->getId();
                if( $calendarListEntry->getPrimary() ) {
                    $sCalendarIdPrimary = $calendarListEntry->getId();
                }
            }
            if( !($opts['pageToken'] = $calendarList->getNextPageToken()) ) {
                break;
            }
        }
        done:
        return( array($raCalendars,$sCalendarIdPrimary) );
    }

    function GetEvents( $calendarId, $startdate, $enddate )
    {
        $optParams = array(
            'orderBy' => 'startTime',
            'singleEvents' => TRUE,
            'timeMin' => date("Y-m-d\TH:i:s\Z", $startdate),
            'timeMax' => date("Y-m-d\TH:i:s\Z", $enddate),
        );
        $results = $this->service->events->listEvents($calendarId, $optParams);

        $raEvents = $results->getItems();

        return( $raEvents );
    }

    function BookSlot( $calendarId, $slot, $sSummary )
    {
        if( ($event = $this->service->events->get($calendarId, $slot)) ) {
            $event->setSummary($sSummary);
            $this->service->events->update($calendarId, $event->getId(), $event);
        }
    }

}


?>
