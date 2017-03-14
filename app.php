<?php
	include 'auth.php';
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
			$limit = 	(!empty($_REQUEST['limit'])) ? $_REQUEST['limit'] : 25;
			$url = 'https://api.mailgun.net/v3/'.$accounts[0][0].'/'.$type.$page.$address.'limit='.$limit;
			$latest = get($url, $accounts[0][1]);

			// For view composite list save is temporary
			// $stack = [];
			// foreach($accounts as $key=>$account) {
			// 	$url = 'https://api.mailgun.net/v3/'.$account[0].'/'.$type.$page.$address.'limit='.$limit;
			// 	array_push($stack, [
			// 		'stats'=>get($url, $account[1])['stats'],
			// 		'account'=>$key
			// 	]);
			// }


			// Parse & set output template
			if(isset($latest['items'])) {
				//Get params
				$out = [
					// 'request'=>$_REQUEST,
					// 'test'=>$latest,
					'status'=>TRUE,
					// 'url'=>$url,
					'object'=>$latest['items'],
					'paging'=>[]
				];
				parse_str(parse_url($latest['paging']['previous'])['query'], $out['paging']['previous']);
				parse_str(parse_url($latest['paging']['next'])['query'], $out['paging']['next']);
				// When count items less limit show for front-end limiter
				if(count($latest['items']) < $limit) $out['paging']['no_more'] = TRUE;
				$out['paging']['first'] = ($current_page == 1) ? TRUE : FALSE;
			}else{
				$out = [
					// 'request'=>$_REQUEST,
					// 'url'=>$url,
					'status'=>FALSE,
					'error'=>"Проверьте интернет соединение, если ошибка повторится свяжитесь с администратором."
				];
			}
			return output($out);
		break;

		case 'latest_events':
			if(!isset($_REQUEST['event'])) return output(['status'=>FALSE,'error'=>'Bad request']);
			$page =		(!empty($_REQUEST['page'])) ? '?page='.$_REQUEST['page'].'&' : '?';
			$current_page = $_REQUEST['current_page'];
			$path = 	json_decode($_REQUEST['path']);
			$event = 	(!empty($_REQUEST['event'])) ? 'event='.$_REQUEST['event'].'&' : '';
			$limit = 	(!empty($_REQUEST['limit'])) ? $_REQUEST['limit'] : 25;
			$url = (empty($path)) ? 'https://api.mailgun.net/v3/'.$accounts[0][0].'/events'.$page.$event.'limit='.$limit : 'https://api.mailgun.net/v3/'.$accounts[0][0].'/events/'.$path;
			$latest = get($url, $accounts[0][1]);
			// Parse & set output template
			if(isset($latest['items'])) {
				//Get params
				$out = [
					'test'=>$latest,
					'status'=>TRUE,
					'url'=>$url,
					'object'=>$latest['items'],
					'paging'=>[]
				];
				// Clean url
				$ll_next = parse_url($latest['paging']['next'])['path'];
				$out['paging']['next'] = [
					'address'=>substr( $ll_next, strpos($ll_next, 'events/')+strlen('events/'), strlen($ll_next) ),
					'limit'=>$limit,
					'page'=>'next'
				];
				$ll_prev = parse_url($latest['paging']['previous'])['path'];
				$out['paging']['previous'] = [
					'address'=>substr( $ll_prev, strpos($ll_prev, 'events/')+strlen('events/'), strlen($ll_prev) ),
					'limit'=>$limit,
					'page'=>'previous'
				];
				// When count items less limit show for front-end limiter
				if(count($latest['items']) < $limit) $out['paging']['no_more'] = TRUE;
				$out['paging']['first'] = ($current_page == 1) ? TRUE : FALSE;
			}else{
				$out = [
					'request'=>$_REQUEST,
					'url'=>$url,
					'status'=>FALSE,
					'error'=>"Проверьте интернет соединение, если ошибка повторится свяжитесь с администратором."
				];
			}
			return output($out);
		break;

		default:
			return output(['status'=>FALSE,'error'=>'Bad request']);
		break;
	}
?>