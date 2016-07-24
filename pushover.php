<?

// TODO: actual rdf parsing to make this less stupid
function as2_to_message($data){
  $msg = "Notification!";
  if(isset($data["name"])){
    $msg = $data["name"];
  }elseif(isset($data["summary"])){
    $msg = $data["summary"];
  }elseif($isset($data["content"])){
    $msg = substr($data["content"], 64)."...";
  }

  if(isset($data["inReplyTo"])){
    $msg .= " in reply to ".$data["inReplyTo"];
  }elseif(isset($data["target"]["@id"])){
    $msg .= " about ".$data["target"]["@id"];
  }

  if(isset($data["actor"]["@id"])){
    $msg .= " from ".$data["actor"]["@id"];
  }

  if(isset($data["object"]["@id"])){
    $msg .= " on ".$data["object"]["@id"];
  }

  return $msg
}

function send_push($msg="New notification", $token, $user){
  curl_setopt_array($ch = curl_init(), array(
    CURLOPT_URL => "https://api.pushover.net/1/messages.json",
    CURLOPT_POSTFIELDS => array(
      "token" => $token,
      "user" => $user,
      "message" => $msg,
    ),
    CURLOPT_SAFE_UPLOAD => true,
  ));
  curl_exec($ch);
  curl_close($ch);
}

?>