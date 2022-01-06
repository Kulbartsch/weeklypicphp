<?php

// source https://gist.github.com/nadar/68a347d2d1de586e4393

/**
 * Send a Message to a Slack Channel.
 *
 * In order to get the API Token visit: https://api.slack.com/custom-integrations/legacy-tokens
 * The token will look something like this `xoxo-2100000415-0000000000-0000000000-ab1ab1`.
 * 
 * @param string $message The message to post into a channel.
 * @param string $channel The name of the channel prefixed with #, example #foobar
 * @return boolean
 */
function slack(string $message, string $channel): bool {

    global $slack_api_token;

    $ch = curl_init("https://slack.com/api/chat.postMessage");
    $data = http_build_query([
      "token" => $slack_api_token,
    	"channel" => $channel, //"#mychannel",
    	"text" => $message, //"Hello, Foo-Bar channel message.",
    	"username" => "WeeklyPic Bot",
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

function slacku($message, $channel) {

  global $slack_api_token;

  $ch = curl_init("https://slack.com/api/chat.postMessage");
  $data = http_build_query([
    "token" => $slack_api_token,
    "user" => $channel, //"#mychannel",
    "text" => $message, //"Hello, Foo-Bar channel message.",
    "username" => "WeeklyPic Bot",
  ]);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $result = curl_exec($ch);
  curl_close($ch);
  
  return $result;
}
?> 