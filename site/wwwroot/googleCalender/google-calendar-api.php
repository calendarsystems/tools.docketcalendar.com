<?php
class GoogleCalendarApi
{
	public function GetAccessToken($client_id, $redirect_uri, $client_secret, $code) {
		$url = 'https://accounts.google.com/o/oauth2/token';

		$curlPost = 'client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $client_secret . '&code='. $code . '&grant_type=authorization_code';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		$data = json_decode(curl_exec($ch), true);
		$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);

		if($http_code != 200)
			throw new Exception('Error : Failed to receieve access token');

		return $data;
	}

	public function GetUserCalendarTimezone($access_token) {
		$url_settings = 'https://www.googleapis.com/calendar/v3/users/me/settings/timezone';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_settings);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token));	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);	
		$data = json_decode(curl_exec($ch), true); //echo '<pre>';print_r($data);echo '</pre>';
		$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);		
		if($http_code != 200) 
			throw new Exception('Error : Failed to get timezone');

		return $data['value'];
	}

	public function GetCalendarsList($access_token) {
		$url_parameters = array();

		$url_parameters['fields'] = 'items(id,summary,timeZone)';
		$url_parameters['minAccessRole'] = 'owner';
        $url_parameters['minAccessRole'] = 'writer';

		$url_calendars = 'https://www.googleapis.com/calendar/v3/users/me/calendarList?'. http_build_query($url_parameters);

		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url_calendars);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$data = json_decode(curl_exec($ch), true); //echo '<pre>';print_r($data);echo '</pre>';
		$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);		
		if($http_code != 200) 
			throw new Exception('Error : Failed to get calendars list');

		return $data['items'];
	}

	public function CreateCalendarEvent($calendar_id, $summary, $all_day, $event_time, $event_timezone, $access_token, $attendees = null, $description = null, $minutes = 1440, $popminutes = 1440, $eventColor = null,$location = null,$status = 'free',$addReminderFlag = null,$addReminderVal = 1440,$addPopUpReminderVal = 1440) {
		
		 $url_events = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendar_id . '/events?sendNotifications=true';

		$curlPost = array('summary' => $summary);
        $curlPost['description'] = $description;
        $curlPost['location'] = $location;
        $curlPost['visibility'] = "public";

        if($status == 'free')
        {
            $curlPost['transparency'] = 'transparent';
        } else {
           $curlPost['transparency'] = 'opaque';
        }

		if($all_day == 1) {
			$curlPost['start'] = array('date' => $event_time['event_date']);
			$curlPost['end'] = array('date' => $event_time['event_date']);
		}
		else {
			$curlPost['start'] = array('dateTime' => $event_time['start_time'], 'timeZone' => $event_timezone);
			$curlPost['end'] = array('dateTime' => $event_time['end_time'], 'timeZone' => $event_timezone);
		}
		
        if($attendees != '')
        {
           $attend_array = array();
           foreach($attendees as $attend)
           {
             if($attend != "") { $attend_array[]["email"] = $attend; }
           }
           $curlPost['attendees'] =  $attend_array;
        }
		
		
		
   if($addReminderFlag > 0)
	{
		$curlPost['reminders'] = array('useDefault' => FALSE, 'overrides' => array(array('method' => 'email', 'minutes' => $minutes),array('method' => 'popup', 'minutes' => $popminutes),array('method' => 'email', 'minutes' => $addReminderVal),array('method' => 'popup', 'minutes' => $addPopUpReminderVal)));
	}
	else
	{
		$curlPost['reminders'] = array('useDefault' => FALSE, 'overrides' => array(array('method' => 'email', 'minutes' => $minutes),array('method' => 'popup', 'minutes' => $popminutes)));
	}
	$curlPost['colorId']  = $eventColor;
	//print_r($curlPost);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_events);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlPost));
		$data = json_decode(curl_exec($ch), true);
       ///echo "<pre>"; print_r($data);  exit();
		$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		//if($http_code != 200)
			//throw new Exception('Error : Failed to create event');

		return $data['id'];
	}

    public function DeleteCalendarEvent($event_id, $calendar_id, $access_token) {
        $url_events = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendar_id . '/events/' . $event_id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_events);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token, 'Content-Type: application/json'));
        $data = json_decode(curl_exec($ch), true);

        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        if($http_code == 410)
        {
           return $http_code;
        } else {
            //if($http_code != 204)
            //throw new Exception('Error : Failed to delete event');
            return 'Error : Failed to delete event';
        }
    }


    public function GetAccountContacts($max_results, $access_token) {

        $url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results='.$max_results.'&alt=json&v=3.0&oauth_token='.$access_token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);

        if($http_code != 200)
            throw new Exception('Error : Failed to receieve access token');

        return $data;
    }
	
	public function UpdateCalendarEvent($event_id,$calendar_id, $summary, $all_day, $event_time, $event_timezone, $access_token, $attendees = null, $description = null, $minutes = 1440,$eventColor = null, $location = null,$status = 'free'){
		
		$url_events = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendar_id . '/events/'.$event_id.'?sendNotifications=true';
		
		$curlPost = array('summary' => $summary);
        $curlPost['description'] = $description;
        $curlPost['location'] = $location;
        $curlPost['visibility'] = "public";

        if($status == 'free')
        {
            $curlPost['transparency'] = 'transparent';
        } else {
           $curlPost['transparency'] = 'opaque';
        }

		if($all_day == 1) {
			$curlPost['start'] = array('date' => $event_time['event_date']);
			$curlPost['end'] = array('date' => $event_time['event_date']);
		}
		else {
			$curlPost['start'] = array('dateTime' => $event_time['start_time'], 'timeZone' => $event_timezone);
			$curlPost['end'] = array('dateTime' => $event_time['end_time'], 'timeZone' => $event_timezone);
		}
		
        if($attendees != '')
        {
           $attend_array = array();
           foreach($attendees as $attend)
           {
             if($attend != "") { $attend_array[]["email"] = $attend; }
           }
           $curlPost['attendees'] =  $attend_array;
        }
		
		
		$curlPost['reminders'] = array('useDefault' => FALSE, 'overrides' => array(array('method' => 'email', 'minutes' => $minutes),array('method' => 'popup', 'minutes' => $minutes)));
		$curlPost['colorId']  = $eventColor;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_events);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlPost));
		$data = json_decode(curl_exec($ch), true);
        //echo "<pre>"; print_r($data);  exit();
		$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		//if($http_code != 200)
			//throw new Exception('Error : Failed to create event');

		return $data['id'];
		
	}
	
	
		public function UpdateNewCalendarEvent($event_id,$calendar_id, $summary, $all_day, $event_time, $event_timezone, $access_token, $attendees = null, $description = null, $minutes = 1440,$popup = 1440,$eventColor, $location = null,$status = 'free'){
		
		$url_events = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendar_id . '/events/'.$event_id.'?sendNotifications=true';
		
		$curlPost = array('summary' => $summary);
        $curlPost['description'] = $description;
        $curlPost['location'] = $location;
        $curlPost['visibility'] = "public";

        if($status == 'free')
        {
            $curlPost['transparency'] = 'transparent';
        } else {
           $curlPost['transparency'] = 'opaque';
        }

		if($all_day == 1) {
			$curlPost['start'] = array('date' => $event_time['event_date']);
			$curlPost['end'] = array('date' => $event_time['event_date']);
		}
		else {
			$curlPost['start'] = array('dateTime' => $event_time['start_time'], 'timeZone' => $event_timezone);
			$curlPost['end'] = array('dateTime' => $event_time['end_time'], 'timeZone' => $event_timezone);
		}
		
        if($attendees != '')
        {
           $attend_array = array();
           foreach($attendees as $attend)
           {
             if($attend != "") { $attend_array[]["email"] = $attend; }
           }
           $curlPost['attendees'] =  $attend_array;
        }
		
		
		$curlPost['reminders'] = array('useDefault' => FALSE, 'overrides' => array(array('method' => 'email', 'minutes' => $minutes),array('method' => 'popup', 'minutes' => $popup)));
		$curlPost['colorId']  = $eventColor;
		//print_r($curlPost);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_events);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlPost));
		$data = json_decode(curl_exec($ch), true);
       // echo "<pre>"; print_r($data);  exit();
		$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		//if($http_code != 200)
			//throw new Exception('Error : Failed to create event');

		return $data['id'];
		
	}
}

?>