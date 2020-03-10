<?
include_once('db.php');
$DB = Database::getInstance();
session_start();

function firebase_curl($user_id, $registrationIds, $title, $message) {
	#API access key from Google API's Console
	define('API_ACCESS_KEY', 'AAAABf4s59s:APA91bHvTpWO6X9Pq8JbGgfauO5QRoYfS4IM8LfJbpR44bWNaCRgiHVWbmKK9ZbyhMM9v30Agu5lWVGP1LlfyGiKzTIDuW5PzIPxPRSND-RcvrVm3_V1J86umBc1FSyNRFL0lcfVZvsE');
	#prep the bundle
    $msg = array(
		'body' => $message,
		'title' => $title,
        'icon' => 'myicon',/*Default Icon*/
        'sound' => 'mySound'/*Default sound*/
    );
	$fields = array(
		'to' => $registrationIds,
		'notification' => $msg,
		'priority' => 'normal',
		'data' => array(
			'user_id' => $user_id,
			'user_name' => $title
		)
	);	
	$headers = array(
		'Authorization: key='.API_ACCESS_KEY,
		'Content-Type: application/json'
	);
	#Send Reponse To FireBase Server	
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
	curl_setopt($ch,CURLOPT_POST, true);
	curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));
	$result = curl_exec($ch);
	curl_close($ch);
	#Echo Result Of FireBase Server
	//echo $result;
}

// Login
if($_REQUEST["action"] == "login" && (isset($_REQUEST["service"]) && !empty($_REQUEST["service"])) && (isset($_REQUEST["user_id"]) && $_REQUEST["user_id"] > 0)) {
	$current_user = $DB->query(
		"SELECT * FROM `users` WHERE `".$_REQUEST["service"]."_id` = ?",
		array(
			"i",
			$_REQUEST["user_id"]
		)
	);
	$api_token = session_id();	
	if(!empty($current_user) && $current_user[0]["id"] >= 1) {
		$result = $DB->query(
			"UPDATE `users` SET `api_token` = ?, `firebase_instance_id` = ? WHERE `id` = ?",
			array(
				"ssi",
				$api_token,
				$_REQUEST["firebase_instance_id"],
				$current_user[0]["id"]
			)
		);
		echo json_encode(array("TYPE" => "OK", "API_TOKEN" => $api_token), JSON_UNESCAPED_UNICODE);
	} else {
		file_put_contents("logs/log_".date("Y-m").".txt", json_encode($_REQUEST, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND);
		$result = $DB->query(
			"INSERT INTO `users` (`api_token`, `firebase_instance_id`, `".$_REQUEST["service"]."_id`, `name`, `birthday`, `sex`, `picture`, `search_man`, `search_woman`, `search_max_age`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
			array(
				"ssissssiii",
				$api_token,
				$_REQUEST["firebase_instance_id"],
				$_REQUEST["user_id"],
				$_REQUEST["user_name"],
				(!empty($user_data["user_birthday"]) ? date("Y-m-d", strtotime($user_data["user_birthday"])) : ""),
				(($_REQUEST["user_gender"] == '2' || $_REQUEST["user_gender"] == 'male') ? 2 : 1),
				$_REQUEST["user_picture"].($_REQUEST["oe"] ? "&oe=".$_REQUEST["oe"] : ""),
				(($_REQUEST["user_gender"] == '2' || $_REQUEST["user_gender"] == 'male') ? 0 : 1),
				(($_REQUEST["user_gender"] == '2' || $_REQUEST["user_gender"] == 'male') ? 1 : 0),
				99
			)
		);		
		if($result > 0) {
			echo json_encode(array("TYPE" => "OK", "API_TOKEN" => $api_token), JSON_UNESCAPED_UNICODE);
		} else {
			echo json_encode(array("TYPE" => "ERROR", "DATA" => $result), JSON_UNESCAPED_UNICODE);
		}
	}
}

// API functions
if(isset($_REQUEST["api_token"])) {
	$current_user = $DB->query(
		"SELECT * FROM `users` WHERE `api_token` = ?",
		array(
			"s",
			$_REQUEST["api_token"]
		)
	);
	if(count($current_user) == 1 && $current_user[0]["id"] > 0) {
		$UserId = $current_user[0]["id"];
		$current_user = $current_user[0];
		
		if($_REQUEST["action"] == "get_list_places") {
			
			$R = 6371;  // earth's radius, km
			// first-cut bounding box (in degrees)
			$latitude = $_REQUEST["latitude"];
			$longitude = $_REQUEST["longitude"];
			$max_distance = 100000; // 0.1 - 1km
			
			if(!empty($latitude) && !empty($longitude)) {
				$max_latitude = $latitude + rad2deg($max_distance/$R);
				$min_latitude = $latitude - rad2deg($max_distance/$R);
				// compensate for degrees longitude getting smaller with increasing latitude
				$max_longitude = $longitude + rad2deg($max_distance/$R/cos(deg2rad($latitude)));
				$min_longitude = $longitude - rad2deg($max_distance/$R/cos(deg2rad($latitude)));

				$resPlaces = $DB->query(
					"SELECT * FROM `places` WHERE `lat` > ? AND `lon` > ? AND `lat` < ? AND `lon` < ? ORDER BY ? DESC LIMIT ?",
					array(
						"ddddsi",
						$min_latitude,
						$min_longitude,
						$max_latitude,
						$max_longitude,
						"id",
						50
					)
				);
			} else {				
				$resPlaces = $DB->query(
					"SELECT * FROM `places` ORDER BY ? DESC LIMIT ?",
					array(
						"si",
						"id",
						10
					)
				);
			}
			foreach($resPlaces as $place) {
				$count_check_in = $DB->query(
					"SELECT COUNT(*) FROM `checked_users` WHERE `place_id` = ? AND `user_id` != ?",
					array(
						"ii",
						$place["id"],
						$UserId
					)
				);
				$place["count_check_in"] = $count_check_in[0]["COUNT(*)"];
				$arPlaces[] = $place;
			}
			echo json_encode(array("TYPE" => "OK", "DATA" => $arPlaces), JSON_UNESCAPED_UNICODE);
		}
		if($_REQUEST["action"] == "get_checked_users" && (isset($_REQUEST["id"]) && $_REQUEST["id"] > 0)) {	
			if($current_user["in_search"] == 1) {		
				$DB->query(
					"REPLACE INTO `checked_users` (`user_id`, `place_id`) VALUES (?, ?)",
					array(
						"ii",
						$UserId,
						$_REQUEST["id"]
					)
				);
			}
			$arCheckedUsers = $DB->query(
				"SELECT * FROM `checked_users` WHERE `place_id` = ? AND `user_id` != ?",
				array(
					"ii",
					$_REQUEST["id"],
					$UserId
				)
			);
			$arCheckedId = "";
			foreach($arCheckedUsers as $checkedUser) {
				$arCheckedId = $arCheckedId.$checkedUser["user_id"].",";
			}
			
			$where = "";
			if($current_user["search_man"] == 1 && $current_user["search_woman"] == 1)
				$where = $where;
			elseif($current_user["search_man"] == 1 && $current_user["search_woman"] == 0)
				$where = $where." AND `sex` = 1";
			elseif($current_user["search_man"] == 0 && $current_user["search_woman"] == 1)
				$where = $where." AND `sex` = 2";
			if($current_user["search_min_age"] > 0)
				$where = $where." AND `birthday` <= '".date("Y-m-d", strtotime("-".$current_user["search_min_age"]." year"))."'";
			if($current_user["search_max_age"] > 0)
				$where = $where." AND `birthday` >= '".date("Y-m-d", strtotime("-".$current_user["search_max_age"]." year"))."'";
			
			$resUsers = $DB->query(
				"SELECT `id`, `name`, `picture`, `birthday` FROM `users` WHERE `id` IN (".substr($arCheckedId, 0, -1).") AND `in_search` = 1".$where
			);
			$resLikes = $DB->query(
				"SELECT * FROM `likes` WHERE `sender_id` = ?",
				array(
					"i",
					$UserId
				)
			);
			$resLikesToo = $DB->query(
				"SELECT * FROM `likes` WHERE `receiver_id` = ?",
				array(
					"i",
					$UserId
				)
			);
			foreach($resLikes as $resLike) {
				$arLikes[$resLike["receiver_id"]] = $resLike;
			}
			foreach($resLikesToo as $resLikeToo) {
				$arLikesToo[$resLikeToo["sender_id"]] = $resLikeToo;
			}
			foreach($resUsers as $resUser) {
				$resUser["like"] = $arLikes[$resUser["id"]]["like"];
				$resUser["like_too"] = $arLikesToo[$resUser["id"]]["like"];
				$arUsers[] = $resUser;
			}
			echo json_encode(array("TYPE" => "OK", "DATA" => $arUsers), JSON_UNESCAPED_UNICODE);
		}
		if($_REQUEST["action"] == "get_user_by_id") {
			$id = $_REQUEST["id"];
			if(empty($_REQUEST["id"]) || $_REQUEST["id"] == 0)
				$id = $UserId;
			$arUser = $DB->query(
				"SELECT `id`, `name`, `birthday`, `picture`, `about` FROM `users` WHERE `id` = ?",
				array(
					"i",
					$id
				)
			);
			echo json_encode(array("TYPE" => "OK", "DATA" => $arUser), JSON_UNESCAPED_UNICODE);
		}
		if($_REQUEST["action"] == "get_settings") {
			$arUser = $DB->query(
				"SELECT `in_search`, `search_min_age`, `search_max_age`, `search_man`, `search_woman`, `notifications_likes`, `notifications_messages` FROM `users` WHERE `id` = ?",
				array(
					"i",
					$UserId
				)
			);
			echo json_encode(array("TYPE" => "OK", "DATA" => $arUser), JSON_UNESCAPED_UNICODE);
		}
		if($_REQUEST["action"] == "set_settings") {	
			$settings = json_decode($_REQUEST["settings"]);		
			$arUser = $DB->query(
				"UPDATE `users` SET `in_search` = ?, `search_man` = ?, `search_woman` = ?, `search_min_age` = ?, `search_max_age` = ?, `notifications_likes` = ?, `notifications_messages` = ? WHERE `id` = ?",
				array(
					"sssssssi",
					$settings[0],
					$settings[1],
					$settings[2],
					$settings[3],
					$settings[4],
					$settings[5],
					$settings[6],
					$UserId
				)
			);
			echo json_encode(array("TYPE" => "OK"), JSON_UNESCAPED_UNICODE);
		}
		if($_REQUEST["action"] == "edit_user") {
			$user_data = json_decode($_REQUEST["user_data"]);
			$id = $_REQUEST["id"];
			if(empty($_REQUEST["id"]) || $_REQUEST["id"] == 0)
				$id = $UserId;
			$arUser = $DB->query(
				"SELECT `id`, `name`, `birthday`, `picture`, `about` FROM `users` WHERE `id` = ?",
				array(
					"i",
					$id
				)
			);
			
			$name = $user_data->name ? $user_data->name : $arUser[0]["name"];
			$birthday = $user_data->birthday ? date("Y-m-d", strtotime($user_data->birthday)) : $arUser[0]["birthday"];
			$picture= $user_data->picture? $user_data->picture: $arUser[0]["picture"];
			$about = $user_data->about ? $user_data->about : $arUser[0]["about"];
			
			if(!empty($_FILES["picture"])) {
				$target_file = "uploads/".basename($_FILES['picture']['name']);
				if(move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
					$picture= "http://".$_SERVER["SERVER_NAME"]."/".$target_file;
				}
			}
			
			$arUser = $DB->query(
				"UPDATE `users` SET `name` = ?, `birthday` = ?, `picture` = ?, `about` = ? WHERE `id` = ?",
				array(
					"ssssi",
					$name,
					$birthday,
					$picture,
					$about,
					$id
				)
			);
			echo json_encode(array("TYPE" => "OK", "DATA" => $arUser), JSON_UNESCAPED_UNICODE);
		}
		if($_REQUEST["action"] == "create_dialog" && !empty($_REQUEST["receiver_id"]) && $_REQUEST["receiver_id"] > 0) {
			$receiver_id = $_REQUEST["receiver_id"];			
			$arDialog = $DB->query(
				"SELECT * FROM `dialogs` WHERE (`sender_id` = ? AND `receiver_id` = ?) OR (`receiver_id` = ? AND `sender_id` = ?)",
				array(
					"iiii",
					$UserId,
					$receiver_id,
					$UserId,
					$receiver_id
				)
			);
			if(!empty($arDialog) && $arDialog[0]["id"] >= 1) {
				echo json_encode(array("TYPE" => "OK", "DATA" => $arDialog), JSON_UNESCAPED_UNICODE);
			} else {
				$result = $DB->query(
					"INSERT INTO `dialogs` (`sender_id`, `receiver_id`) VALUES(?, ?)",
					array(
						"ii",
						$UserId,
						$receiver_id
					)
				);				
				if($result > 0) {
					echo json_encode(array("TYPE" => "OK", "DATA" => $result), JSON_UNESCAPED_UNICODE);
				} else {
					echo json_encode(array("TYPE" => "ERROR", "DATA" => $result), JSON_UNESCAPED_UNICODE);
				}
			}
		}
		if($_REQUEST["action"] == "get_messages" && !empty($_REQUEST["id"]) && $_REQUEST["id"] > 0) {
			$id = $_REQUEST["id"];
			$outMessages = $DB->query(
				"SELECT * FROM `messages` WHERE `sender_id` = ? AND `receiver_id` = ? ORDER BY `date_create` DESC LIMIT ?",
				array(
					"iii",
					$UserId,
					$id,
					15
				)
			);
			$inMessages = $DB->query(
				"SELECT * FROM `messages` WHERE `receiver_id` = ? AND `sender_id` = ? ORDER BY `date_create` DESC LIMIT ?",
				array(
					"iii",
					$UserId,
					$id,
					15
				)
			);
			$messages = array_merge($outMessages, $inMessages);
			function cmp($a, $b) {
				$a = strtotime($a["date_create"]);
				$b = strtotime($b["date_create"]);
				if ($a == $b) {
					return 0;
				}
				return ($a < $b) ? -1 : 1;
			}
			usort($messages, "cmp");
			foreach($messages as $key => $message) {
				if($message["sender_id"] == $UserId)
					$message["type"] = "out";
				else
					$message["type"] = "in";
				$arMessages[] = $message;
			}
			
			$to_message_user = $DB->query(
				"SELECT * FROM `users` WHERE `id` = ?",
				array(
					"i",
					$id
				)
			);
			echo json_encode(array("TYPE" => "OK", "DATA" => $arMessages, "USERS" => array("CURRENT" => $current_user, "TO_MESSAGE" => $to_message_user[0])), JSON_UNESCAPED_UNICODE);
		}
		if($_REQUEST["action"] == "add_message" && !empty($_REQUEST["message"]) && !empty($_REQUEST["receiver_id"]) && $_REQUEST["receiver_id"] > 0) {
			$receiver_id = $_REQUEST["receiver_id"];
			$message = $_REQUEST["message"];
			$result = $DB->query(
				"INSERT INTO `messages` (`sender_id`, `receiver_id`, `message`) VALUES(?, ?, ?)",
				array(
					"iis",
					$UserId,
					$receiver_id,
					$message
				)
			);
			
			$receiver_user = $DB->query(
				"SELECT * FROM `users` WHERE `id` = ?",
				array(
					"i",
					$receiver_id
				)
			);
			if($receiver_user[0]["notifications_messages"])
				firebase_curl($UserId, $receiver_user[0]["firebase_instance_id"], $current_user["name"], $message);
			if($result > 0) {
				echo json_encode(array("TYPE" => "OK", "DATA" => $result), JSON_UNESCAPED_UNICODE);
			} else {
				echo json_encode(array("TYPE" => "ERROR", "DATA" => $result), JSON_UNESCAPED_UNICODE);
			}
		}
		if($_REQUEST["action"] == "like" && isset($_REQUEST["like"]) && !empty($_REQUEST["receiver_id"]) && $_REQUEST["receiver_id"] > 0) {
			$receiver_id = $_REQUEST["receiver_id"];
			$like = $_REQUEST["like"];
			$arLike = $DB->query(
				"SELECT * FROM `likes` WHERE `sender_id` = ? AND `receiver_id` = ?",
				array(
					"ii",
					$UserId,
					$receiver_id
				)
			);
			if($arLike[0]["id"] > 0) {
				$result = $DB->query(
					"UPDATE `likes` SET `like` = ? WHERE `id` = ?",
					array(
						"ii",
						$like,
						$arLike[0]["id"]
					)
				);
			} else {
				$result = $DB->query(
					"INSERT INTO `likes` (`sender_id`, `receiver_id`, `like`) VALUES(?, ?, ?)",
					array(
						"iii",
						$UserId,
						$receiver_id,
						$like
					)
				);				
			}			
			if($receiver_user[0]["notifications_likes"])
				firebase_curl($UserId, $receiver_user[0]["firebase_instance_id"], $current_user["name"], $current_user["name"]." лайкнул вас");
			if($result > 0) {
				echo json_encode(array("TYPE" => "OK", "DATA" => $result), JSON_UNESCAPED_UNICODE);
			} else {
				echo json_encode(array("TYPE" => "ERROR", "DATA" => $result), JSON_UNESCAPED_UNICODE);
			}
		}
		if($_REQUEST["action"] == "get_dialogs") {			
			$resDialogs = $DB->query(
				"SELECT * FROM `dialogs` WHERE `sender_id` = ? OR `receiver_id` = ?",
				array(
					"ii",
					$UserId,
					$UserId
				)
			);
			
			$arDialogs = array();
			$arReceiverIds = "";
			
			foreach($resDialogs as $dialog) {
				$resMessages = $DB->query(
					"SELECT `message`, `date_create` FROM `messages` WHERE `dialog_id` = ".$dialog["id"]." ORDER BY `date_create` DESC LIMIT 1"
				);
				$arDialogs[$dialog["receiver_id"]] = array(
					"user_id" => $dialog["receiver_id"],
					"last_message" => $resMessages[0]["message"],
					"last_message_timestamp" => date("d.m.Y H:i:s", strtotime($resMessages[0]["date_create"]))
				);
				
				$arReceiverIds = $arReceiverIds.$dialog["receiver_id"].",";
			}
			$resReceivers = $DB->query(
				"SELECT `id`, `name`, `picture` FROM `users` WHERE `id` IN (".substr($arReceiverIds, 0, -1).")"
			);
			
			foreach($resReceivers as $receiver) {
				foreach($arDialogs as $dialog) {
					if($receiver["id"] == $dialog["user_id"]) {					
						$arDialogs[$dialog["user_id"]]["user_name"] = $receiver["name"];					
						$arDialogs[$dialog["user_id"]]["user_picture"] = $receiver["picture"];
					}
				}
			}
			$arDialogs = array_values($arDialogs);
			echo json_encode(array("TYPE" => "OK", "DATA" => $arDialogs), JSON_UNESCAPED_UNICODE);
		}
	} else {
		file_put_contents("logs/log_".date("Y-m").".txt", json_encode($current_user, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND);
		echo json_encode(array("TYPE" => "ERROR", "DATA" => "unknown token"), JSON_UNESCAPED_UNICODE);
	}
}
?>