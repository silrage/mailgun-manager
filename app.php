<?php
	include 'auth.php';

	function setTimeZone(){
		return date_default_timezone_set('Europe/Moscow');
		// return date_default_timezone_set('Europe/Kaliningrad');
	};
	function humanDateToEpoch($str){
		if($str) {
			setTimeZone();
			$date = date_create($str); 
			return date_format($date, 'U');
		}else{
			return NULL;
		}
	}
	function getSplitDate($epoch) {
		$monthes = array(
		  1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
		  5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
		  9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
		);
		setTimeZone();
		$nowDate = date('j,n');
		$date = date('d.m.Y', $epoch);
		$dateMonth = date('j', $epoch) . ' '. $monthes[(date('n', $epoch))];
		$time = date('H:i:s', $epoch);
		return array( 'date'=>$date, 'date_month'=>$dateMonth, 'time'=>$time, 'now'=>$nowDate );
	}

	function _isCurl(){
	    return function_exists('curl_version');
	}
	function output($obj, $type='JSON') {
		if($type==='JSON') {
			header('Content-Type: application/json');
			print_r( json_encode($obj) );
		}else{
			print_r( $obj);
		}
	}
	// Sorting
	function cmp($a, $b) {
	    return strcmp($b["timestamp"], $a["timestamp"]);
	}
	function get($url, $api_key) {
		if(_isCurl()) {
			// Connect
			$request = curl_init( $url );
	    	curl_setopt($request, CURLOPT_USERPWD, "api:".$api_key); 
	    	// curl_setopt($request, CURLOPT_POST, TRUE);
	    	curl_setopt($request, CURLOPT_CUSTOMREQUEST, "GET");
	    	curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	    	// curl_setopt($request, CURLOPT_HEADER, TRUE);
			// curl_setopt(
			//     $request,
			//     CURLOPT_POSTFIELDS,
		 //        $data
			// );
			curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);
			$respload = curl_exec($request);
			curl_close($request);
			return json_decode($respload, TRUE);
		}else{
			return 'CURL is not run!';
		}
	}


	$task = $_REQUEST['task'];
	if(!isset($task)) return output(['status'=>FALSE,'error'=>'Запрашиваемая вами страница не найдена'], FALSE);

	if(!is_file('config.php')) {
		return output(['status'=>FALSE,'error'=>'Приложение не инициализировано..']);
	}
	include 'config.php';

	switch($task){
		case 'auth':
			if(!empty($_REQUEST['login']) && !empty($_REQUEST['password'])) {
				// check user
				if(isset($users[$_REQUEST['login']])) {
					if($users[$_REQUEST['login']] === $_REQUEST['password']) {
						auth(['username'=>$_REQUEST['login']]);
						return output(['status'=>TRUE, 'error'=>'check']);
					}
				}
				return output(['status'=>FALSE, 'error'=>'Ошибка авторизации. не верный логин и/или пароль', 'test'=>$_REQUEST]);
			}else{
				return output(['status'=>FALSE, 'error'=>'Введите логин и пароль']);
			}
		break;

		case 'logout':
			if(isset($_SESSION['username'])){
				logout();
			}else{
				return output(['session'=>$_SESSION,'status'=>FALSE, 'error'=>'Ошибка запроса']);
			}
		break;

		case 'analytics':
			$stack = [];
			foreach($accounts as $key=>$account) {
				$url = 'https://api.mailgun.net/v3/'.$account[0].'/stats/total?event=delivered&event=failed&event=opened&event=clicked&event=unsubscribed&duration=31d';
				array_push($stack, [
					'stats'=>get($url, $account[1])['stats'],
					'account'=>$key
				]);
			}
			return output($stack);
		break;

		case 'latest_posts':
			if(!isset($_REQUEST['type'])) return output(['status'=>FALSE,'error'=>'Bad request']);
			$page =		(!empty($_REQUEST['page'])) ? '?page='.$_REQUEST['page'] : '?';
			$current_page = $_REQUEST['current_page'];
			$address =	(!empty($_REQUEST['address'])) ? '&address='.$_REQUEST['address'].'&' : '';
			$type = 	(!empty($_REQUEST['type'])) ? $_REQUEST['type'] : 'bounces';
			// Limit per account
			$limit = 	50;

			// For composite view get full list
			$stack = [];
			$out = [
				'status'=>FALSE,
				'object'=>[]
			];
			foreach($accounts as $key=>$account) {
				$url = 'https://api.mailgun.net/v3/'.$account[0].'/'.$type.$page.$address.'limit='.$limit;
				array_push($stack, [
					'latest'=>get($url, $account[1]),
					'account'=>$key
				]);
			}

			// Clean view
			/* 
				[
					[ 'latest' => ['items'=>[]], 'account'=>'name-1' ],
					[ 'latest' => [], 'account'=>'name-2' ]
				]
			*/
			$limitPosts = (!empty($_REQUEST['limit'])) ? $_REQUEST['limit'] : 50;
			$set = 0;
			foreach($stack as $latest) {
				if(isset($latest['latest']['items'])) {
					// Push to out
					foreach($latest['latest']['items'] as $item) {
						//Get date object`
						$epoch = humanDateToEpoch($item['created_at']);
						$date = getSplitDate($epoch);
						$object = [
							'address'=>$item['address'],
							'timestamp'=>$epoch,
							'created_at'=>$date['date'].' '.$date['time']
						];
						if($set < $limitPosts) {
							$set++;
							$out['object'][] = $object;
						}
					}
				}
			}
			$out['count'] = $limitPosts;
			// When found for one item status is success
			if(isset($out['object'][0])) {
				$out['status'] = TRUE;
				usort($out['object'], "cmp");
			}else{
				$out['error'] = "Данных пока нет";
			}
			return output($out);
		break;

		case 'latest_events':
			if(!isset($_REQUEST['event'])) return output(['status'=>FALSE,'error'=>'Bad request']);
			$page =		(!empty($_REQUEST['page'])) ? '?page='.$_REQUEST['page'].'&' : '?';
			$current_page = $_REQUEST['current_page'];
			$path = 	json_decode($_REQUEST['path']);
			$event = 	(!empty($_REQUEST['event'])) ? 'event='.$_REQUEST['event'].'&' : '';
			// Limit per account
			$limit = 	50;

			// For composite view get full list
			$stack = [];
			$out = [
				'status'=>FALSE,
				'object'=>[]
			];
			foreach($accounts as $key=>$account) {
				$url = 'https://api.mailgun.net/v3/'.$account[0].'/events'.$page.$event.'limit='.$limit;
				array_push($stack, [
					'latest'=>get($url, $account[1]),
					'account'=>$key,
					'url'=>$url
				]);
			}

			//Clean view
			$limitEvents = (!empty($_REQUEST['limit'])) ? $_REQUEST['limit'] : 50;
			$set = 0;
			$out['test'] = $stack;
			foreach($stack as $events) {
				if(isset($events['latest']['items'])) {
					// Push to out
					foreach($events['latest']['items'] as $item) {
						//Get date object`
						$epoch = $item['timestamp'];
						$date = getSplitDate($epoch);
						$object = [
							'address'=>$item['recipient'],
							'timestamp'=>$epoch,
							'date'=>$date['date'].' '.$date['time'],
							'geolocation'=>$item['geolocation']
						];
						if($set < $limitEvents) {
							$set++;
							$out['object'][] = $object;
						}
					}
				}
			}
			$out['count'] = $limitEvents;
			// When found for one item status is success
			if(isset($out['object'][0])) {
				$out['status'] = TRUE;
				usort($out['object'], "cmp");
			}else{
				$out['error'] = "Данных пока нет";
			}
			return output($out);
		break;

		default:
			return output(['status'=>FALSE,'error'=>'Bad request']);
		break;
	}
?>