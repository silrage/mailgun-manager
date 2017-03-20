<?php
	// Developer sets
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

	include 'auth.php';

	// var_dump( is_auth() );
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Mailgun API</title>
	<link rel="stylesheet" href="fonts/font-awesome.min.css">
	<link rel="stylesheet" href="bootstrap.min.css">
	<style type="text/css">
		.box {

		}
		.box h3 {}
		.box .item {
			margin-bottom: 15px;
		}
		.box .nav {}
		.box .nav a {
			border: 1px solid #ccc;
			display: inline-block;
			margin: 5px;
			padding: 10px;
		}
		.loader {
		  position: fixed;
		  margin: auto;
		  width: 100px;
		  top: 0;
		  bottom: 0;
		  left: 0;
		  right: 0;
		  z-index: 9999;
		}
		.loader:before {
			content: '';
			display: block;
			padding-top: 100%;
		}
		.loader.hidden {
			-moz-opacity: 0;
			opacity: 0;
		}
		.circular {
		  background: transparent;
		  animation: rotate 1s linear infinite;
		  height: 100%;
		  transform-origin: center center;
		  width: 100%;
		  position: absolute;
		  top: 0;
		  bottom: 0;
		  left: 0;
		  right: 0;
		  margin: auto;
		}
		.path {
		  stroke-dasharray: 1, 200;
		  stroke-dashoffset: 0;
		  animation: dash 1.0s ease-in-out infinite, color 6s ease-in-out infinite;
		  stroke-linecap: round;
		}
		@keyframes rotate {
		  100% {
		    transform: rotate(360deg);
		  }
		}
		@keyframes dash {
		  0% {
		    stroke-dasharray: 1, 200;
		    stroke-dashoffset: 0;
		  }
		  50% {
		    stroke-dasharray: 89, 200;
		    stroke-dashoffset: -35px;
		  }
		  100% {
		    stroke-dasharray: 89, 200;
		    stroke-dashoffset: -124px;
		  }
		}
		@keyframes color {
		  100%,
		  0% {
		    fill: #70cf00;
		  }
		  40% {
		    fill: #42d200;
		  }
		  66% {
		    fill: #081997;
		  }
		  80%,
		  90% {
		    fill: #0042e8;
		  }
		}
	</style>
	<script type="text/javascript" src="jquery.min.js"></script>
</head>
<body class="no-js">

	<div class="container">
		<div class="jumbotron">
			<h1>Mailgun - менеджер</h1>
			<p>Транспорт для рассылок, аналитика</p>
			<a class="pull-right" href="https://github.com/v3ctor/mailgun-manager">silrage</a>
			<span class="pull-right">ver. 0.0.1</span>
		</div>

		<?php
			if(!is_auth()){
			?>
				<form action="app.php">
					<input type="text" placeholder="Логин" name="login" />
					<input type="password" placeholder="Пароль" name="password" />
					<div class="messages bg-danger"></div>
					<input type="hidden" name="task" value="auth" />
					<input type="submit" value="Авторизоваться" />
				</form>

	<script type="text/javascript">
		'use strict'

		// Get transport
		function get_auth(obj, callback) {
			$.ajax('app.php?task=auth&login='+obj.login+'&password='+obj.password).done(function(resp){
				return callback(resp);
			})
		}

		$('input[type=submit]').on('click', function(e){
			e.preventDefault();
			get_auth( {
				login: $('input[name=login]').val(),
				password: $('input[name=password]').val()
			}, function(resp){
				if(resp.status) {
					$('form .messages').html('');
					window.location.reload();
				}else{
					$('form .messages').html(resp.error);
				}
			});
		})

	</script>
			<?php
			}else{
				$user = user();
			?>

				<div class="login-panel pull-right">
					<p class="username"><?=$user['username'];?></p>
					<a class="btn logout" onclick="logout()">Выйти</a>
				</div>
				
				<div class="row">
					<div id="diagram" class="col-md-12" style="height: 600px"></div>
				</div>

				<div class="row">
					<div class="box col-md-6">
						<h3>Последняя активность:</h3>
						<div class="latest-event form-group">
							<select name="" class="form-control">
								<option value="clicked">Клики</option>
								<option value="opened">Просмотры</option>
							</select>
						</div>
						<div class="latest-events-limit form-group">
							<input type="hidden" class="field form-control" value="50" />
						</div>
						<div class="latest-events"></div>
					</div>

					<div class="box col-md-6">
						<h3>Последняя отправка писем:</h3>
						<div class="latest-post form-group">
							<select name="" class="form-control">
								<option value="complaints">Пожаловались на спам</option>
								<option value="bounces">Блокированы</option>
								<option value="unsubscribes">Отписались</option>
							</select>
						</div>
						<div class="latest-post-limit form-group">
							<input type="hidden" class="field form-control" value="50" />
						</div>
						<div class="latest-posts"></div>
					</div>
				</div>


	<script type="text/javascript" src="anychart-bundle.min.js"></script>
	<script type="text/javascript">
		'use strict'
		var loader = `
		<div class="loader main">
	      <svg class="circular" viewBox="0 0 100 100">
	        <path class="path" d="M10,50c0,0,0,0.5,0.1,1.4c0,0.5,0.1,1,0.2,1.7c0,0.3,0.1,0.7,0.1,1.1c0.1,0.4,0.1,0.8,0.2,1.2c0.2,0.8,0.3,1.8,0.5,2.8 c0.3,1,0.6,2.1,0.9,3.2c0.3,1.1,0.9,2.3,1.4,3.5c0.5,1.2,1.2,2.4,1.8,3.7c0.3,0.6,0.8,1.2,1.2,1.9c0.4,0.6,0.8,1.3,1.3,1.9 c1,1.2,1.9,2.6,3.1,3.7c2.2,2.5,5,4.7,7.9,6.7c3,2,6.5,3.4,10.1,4.6c3.6,1.1,7.5,1.5,11.2,1.6c4-0.1,7.7-0.6,11.3-1.6 c3.6-1.2,7-2.6,10-4.6c3-2,5.8-4.2,7.9-6.7c1.2-1.2,2.1-2.5,3.1-3.7c0.5-0.6,0.9-1.3,1.3-1.9c0.4-0.6,0.8-1.3,1.2-1.9 c0.6-1.3,1.3-2.5,1.8-3.7c0.5-1.2,1-2.4,1.4-3.5c0.3-1.1,0.6-2.2,0.9-3.2c0.2-1,0.4-1.9,0.5-2.8c0.1-0.4,0.1-0.8,0.2-1.2 c0-0.4,0.1-0.7,0.1-1.1c0.1-0.7,0.1-1.2,0.2-1.7C90,50.5,90,50,90,50s0,0.5,0,1.4c0,0.5,0,1,0,1.7c0,0.3,0,0.7,0,1.1 c0,0.4-0.1,0.8-0.1,1.2c-0.1,0.9-0.2,1.8-0.4,2.8c-0.2,1-0.5,2.1-0.7,3.3c-0.3,1.2-0.8,2.4-1.2,3.7c-0.2,0.7-0.5,1.3-0.8,1.9 c-0.3,0.7-0.6,1.3-0.9,2c-0.3,0.7-0.7,1.3-1.1,2c-0.4,0.7-0.7,1.4-1.2,2c-1,1.3-1.9,2.7-3.1,4c-2.2,2.7-5,5-8.1,7.1 c-0.8,0.5-1.6,1-2.4,1.5c-0.8,0.5-1.7,0.9-2.6,1.3L66,87.7l-1.4,0.5c-0.9,0.3-1.8,0.7-2.8,1c-3.8,1.1-7.9,1.7-11.8,1.8L47,90.8 c-1,0-2-0.2-3-0.3l-1.5-0.2l-0.7-0.1L41.1,90c-1-0.3-1.9-0.5-2.9-0.7c-0.9-0.3-1.9-0.7-2.8-1L34,87.7l-1.3-0.6 c-0.9-0.4-1.8-0.8-2.6-1.3c-0.8-0.5-1.6-1-2.4-1.5c-3.1-2.1-5.9-4.5-8.1-7.1c-1.2-1.2-2.1-2.7-3.1-4c-0.5-0.6-0.8-1.4-1.2-2 c-0.4-0.7-0.8-1.3-1.1-2c-0.3-0.7-0.6-1.3-0.9-2c-0.3-0.7-0.6-1.3-0.8-1.9c-0.4-1.3-0.9-2.5-1.2-3.7c-0.3-1.2-0.5-2.3-0.7-3.3 c-0.2-1-0.3-2-0.4-2.8c-0.1-0.4-0.1-0.8-0.1-1.2c0-0.4,0-0.7,0-1.1c0-0.7,0-1.2,0-1.7C10,50.5,10,50,10,50z" fill="#70cf00" >
	        </path>
	      </svg>
	    </div>
		`;

		// Get transport
		function logout() {
			$.ajax('app.php?task=logout').done(function(){
				window.location.reload();
			})
		}
		function get_analytics(obj, callback) {
			$.ajax('app.php?task=analytics').done(function(resp){
				return callback(resp);
			})
		}
		function get_latest_events(obj, callback) {
			$.ajax('app.php?task=latest_events&event='+obj.event+'&path='+JSON.stringify(obj.path)+'&limit='+obj.limit).done(function(resp){
				return callback(resp);
			})
		}
		function get_latest_posts(obj, callback) {
			$.ajax('app.php?task=latest_posts&type='+obj.type+'&page='+obj.page+'&current_page='+obj.current_page+'&address='+obj.address+'&limit='+obj.limit).done(function(resp){
				return callback(resp);
			})
		}

		// Other functions
		var months = [
			"01",
			"02",
			"03",
			"04",
			"05",
			"06",
			"07",
			"08",
			"09",
			"10",
			"11",
			"12",
		];
		function navs(paging) {
			var content = '';
			content += '<div class="nav">';
			if(!paging.first)
				content += '<a href="javascript:void(0)" data-address="'+paging.previous.address+'" data-page="'+paging.previous.page+'" data-limit="'+paging.previous.limit+'" class="btn btn-default"><</a>';
			if(!paging.no_more)
				content += '<a href="javascript:void(0)" data-address="'+paging.next.address+'" data-page="'+paging.next.page+'" data-limit="'+paging.next.limit+'" class="btn btn-default">></a>';
			content += '</div>';
			return content;
		}
		function put_latest_events(params) {
			$('.box .latest-events').html(loader);
			get_latest_events(params, function(result){
				var content = '';
				if(result.status) {
					// content += navs(result.paging);
					content += '<table class="table table-striped">';
					$.each(result.object, function(i,v){
						// var offset = -3,
						// 	d = new Date(0),
						// 	date = new Date( d.setUTCSeconds(v.timestamp) );
						content += 
							`<tr class="item">
								<td class="created">`+v.date+`</td>
								<td class="email"><b>`+v.address+`</b></td>
								`;
								if(v.campaign.status){
									content += `
									<td class="campaign">
										<span class="title">`+v.campaign.name+`</span>
										<a href="`+v.campaign.url+`" target="_blank">
											<i class="fa fa-external-link"></i>
										</a></td>`;
								}
						content += `
								<td class="geo" title="`+v.geolocation.country+`">`+v.geolocation.city+`</td>
							</tr>`;
					});
					content += '</table>';
					// content += navs(result.paging);
				}else{
					var content = result.error;
				}
				$('.box .latest-events').html(content);
				$('.box .latest-events .nav a').on('click', function(e){
					current_page = ($(this).attr('data-page') == 'next')?current_page+1:current_page-1
					put_latest_events({
						event: $('.latest-event select').val(),
						page: $(this).attr('data-page'),
						current_page: current_page,
						path: $(this).attr('data-address'),
						limit: $(this).attr('data-limit')
					})
				});
				$('.latest-event select').on('change', function(e){
					current_page = 1;
					put_latest_events({
						event: $(this).val(),
						page: '',
						current_page: current_page,
						address: '',
						limit: $('.latest-events-limit input').val()
					})
				});
			});
		}
		function put_latest_post(params) {
			$('.box .latest-posts').html(loader);
			get_latest_posts(params, function(result){
				var content = '';
				if(result.status) {
					// content += navs(result.paging);
					content += '<table class="table table-striped">';
					$.each(result.object, function(i,v){
						content += 
							`<tr class="item">
								<td class="created">`+v.created_at+`</td>
								<td class="email"><b>`+v.address+`</b></td>
								<td class="code" title="`+v.error+`">`+v.code+v.tag+`</td>
							</tr>`;
					});
					content += '</table>';
					// content += navs(result.paging);
				}else{
					var content = result.error;
				}
				$('.box .latest-posts').html(content);
				$('.box .latest-posts .nav a').on('click', function(e){
					current_page = ($(this).attr('data-page') == 'next')?current_page+1:current_page-1
					$('.box .latest-posts').html(loader);
					put_latest_post({
						type: $('.latest-post select').val(),
						page: $(this).attr('data-page'),
						current_page: current_page,
						address: $(this).attr('data-address'),
						limit: $(this).attr('data-limit')
					});
				});
				$('.latest-post select').on('change', function(e){
					current_page = 1;
					$('.box .latest-posts').html(loader);
					put_latest_post({
						type: $(this).val(),
						page: '',
						current_page: current_page,
						address: '',
						limit: $('.latest-post-limit input').val()
					});
				});
			});
		}

		// Multi-pager
		var current_page = {};
		current_page.latest_events = 1;
		current_page.latest_post = 1;

		function stats(data, callback) {
			var stats = {
				line1: [],
				line2: [],
				line3: []
			};
			$.each(data, function(id, account) {
				$(account.stats).each(function(i,v){
					var mm = new Date( v.time ),
						month = months[ mm.getMonth() ],
						day = mm.getDate();
					// stats.line1.push(
					// 	{
					// 		x: day+'.'+month,
					// 		name: 'Кликнули',
					// 		value: v.clicked.total
					// 	}
					// );
					if(!stats.line1[i]) {
						stats.line1[i] = {
							x: day+'.'+month,
							name: 'Кликнули',
							value: v.clicked.total
						};
					}else{
						stats.line1[i].value += v.clicked.total;
					}
					if(!stats.line2[i]) {
						stats.line2[i] = {
							x: day+'.'+month,
							name: 'Открытий',
							value: v.opened.total
						}
					}else{
						stats.line2[i].value += v.opened.total;
					}
					if(!stats.line3[i]) {
						stats.line3[i] = {
							x: day+'.'+month,
							name: 'Доставлено',
							value: v.delivered.total
						}
					}else{
						stats.line3[i].value += v.delivered.total;
					}
				});
			});
			return callback(stats);
		}

		// Put analytics
		get_analytics({}, function(result){
			stats(result, function(stat){
				anychart.onDocumentReady(function() {
					var chart = anychart.column();
					chart.title("Последняя статистика по рассылкам");
					chart.container("diagram")
					// Clicks
					var line1 = chart.column(stat.line1);
					line1.fill("#FF3333", 1);
					line1.stroke("#FF3333");
					line1.hoverStroke("#FF3333", 2);
					line1.selectStroke("#FF3333", 4);
					var line1Tooltip = line1.tooltip();
					line1Tooltip.textFormatter("{%name}: {%Value}");
					// Opens
					var line2 = chart.column(stat.line2);
					line2.fill("#4fff43", 1);
					line2.stroke("#4fff43");
					line2.hoverStroke("#4fff43", 2);
					line2.selectStroke("#4fff43", 4);
					var line2Tooltip = line2.tooltip();
					line2Tooltip.textFormatter("{%name}: {%Value}");
					// Delivered
					var line3 = chart.column(stat.line3);
					line3.fill("#8df4ff", 1);
					line3.stroke("#8df4ff");
					line3.hoverStroke("#8df4ff", 2);
					line3.selectStroke("#8df4ff", 4);
					var line3Tooltip = line3.tooltip();
					line3Tooltip.textFormatter("{%name}: {%Value}");

					chart.draw();
			    });
			});
		});
		// Put content in boxes automatically
		put_latest_events({
			event: $('.latest-event select').val(),
			path: '',
			current_page: current_page.latest_events,
			limit: $('.latest-events-limit input').val()
		});
		put_latest_post({
			type: $('.latest-post select').val(),
			page: '',
			current_page: current_page.latest_post,
			address: '',
			limit: $('.latest-post-limit input').val()
		});


	</script>

			<?php
			}
		?>
		
		<footer class="row">
			<div class="col-md-12 copyright">
				<span class="pull-right">Копирование и распространие без уведомления <a href="https://github.com/v3ctor">разработчика</a> запрещено.</span>
			</div>
		</footer>
	</div>

</body>
</html>