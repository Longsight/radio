<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

require '../vendor/autoload.php';
require_once("sonos.class.php");

Dotenv::load(__DIR__);

$sonosIP = getenv('SONOS_IP');
$spotifyUser = getenv('SPOTIFY_USER');
$masterPlaylist = getenv('MASTER_PLAYLIST');

$session = new SpotifyWebAPI\Session(
    getenv('SPOTIFY_CLIENT_ID'),
    getenv('SPOTIFY_CLIENT_SECRET'),
    'http://radio.tsr.local');

$api = new SpotifyWebAPI\SpotifyWebAPI();

$scopes = array(
    'playlist-read-private',
    'user-read-private'
);

$session->requestCredentialsToken($scopes);
$accessToken = $session->getAccessToken();

$api->setAccessToken($accessToken);

$sonos = new SonosPHPController($sonosIP);
$pos = $sonos->GetPositionInfo();

if(array_key_exists('TrackURI', $pos)){
    $track = $pos['TrackURI'];
}

if($track){
    preg_match( '/x-sonos-spotify:spotify:track:([0-9a-zA-Z]+)/', urldecode($track), $match );
    $sonosTrack = $match[1];

    $curTrack = $api->getTrack($sonosTrack);

    $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $pos['RelTime']);
    sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
    $relTime = $hours * 3600 + $minutes * 60 + $seconds;

    $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $pos['TrackDuration']);
    sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
    $trackDuration = $hours * 3600 + $minutes * 60 + $seconds;

    $timeRemaining = $trackDuration - $relTime;
}

$tracks = $api->getUserPlaylistTracks($spotifyUser, $masterPlaylist);
$items = $tracks->items;
$count = count($items);
$offset = 100;

while($count < $tracks->total) {
    $set = $api->getUserPlaylistTracks($spotifyUser, $masterPlaylist, array('offset' => $offset));
    $items = array_merge($items, $set->items);
    $count = count($items);
    $offset = $offset + 100;
}

$start = array();
$mid = array();
$final = array();
$found = false;

foreach($items as $item){

    if($found){
        $mid[] = $item;
    } else {
        if($item->track->id === $sonosTrack) {
            $found = true;
            continue;
        }
        $start[] = $item; 
    }
}

$final = array_merge($mid, $start);

function formatMilliseconds($milliseconds) {
    $seconds = floor($milliseconds / 1000);
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $seconds = $seconds % 60;
    $minutes = $minutes % 60;

    if($hours){
        $format = '%u hr %02u min';
        $time = sprintf($format, $hours, $minutes, $seconds);
    } else {
        $format = '%02u:%02u';
        $time = sprintf($format, $minutes, $seconds);
    }
    return $time;
}

?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<?php if($timeRemaining > 10) { ?><meta http-equiv="refresh" content="<?=$timeRemaining?>;URL='/'" /><?php } else { ?><meta http-equiv="refresh" content="20;URL='/'" /><?php } ?>
<meta charset="utf-8" />
<title>TSR Radio</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta content="" name="description" />
<meta content="" name="author" />

<!-- BEGIN CORE CSS FRAMEWORK -->
<link href="assets/plugins/boostrapv3/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/plugins/boostrapv3/css/bootstrap-theme.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/plugins/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css"/>
<!-- END CORE CSS FRAMEWORK -->

<!-- BEGIN CSS TEMPLATE -->
<link href="assets/css/style.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/responsive.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/custom-icon-set.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/magic_space.css" rel="stylesheet" type="text/css"/>
<!-- END CSS TEMPLATE -->

<style>
.page-content.condensed {
    margin-left: 0px;
}
.page-content .content {
    padding-top: 10px;
}
</style>

</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="">

<!-- BEGIN CONTAINER -->
<div class="page-container row-fluid">

  <!-- BEGIN PAGE CONTAINER-->
  <div class="page-content condensed">
    <div class="clearfix"></div>
    <div class="content sm-gutter">
      <div class="page-title">
        <h1>TSR Radio</h1>
      </div>
     
      <div class="row" >
       <div class="col-md-12 col-vlg-4 col-sm-12">
          <div class="row">
          
          <?php if($curTrack){ ?>
          <div class="col-md-4 col-sm-6 m-b-10"  data-aspect-ratio="true">
            <div class="live-tile slide ha ">
              <div class="slide-front ha tiles green ">
                <div class="overlayer top-left fullwidth">
                  <div class="overlayer-wrapper">
                    <div class="tiles gradient-black p-l-20 p-r-20 p-b-20 p-t-20">
                      <h4 class="text-white semi-bold no-margin">Now Playing...</h4>
                    </div>
                  </div>
                </div>
                <div class="overlayer bottom-left fullwidth">
                  <div class="overlayer-wrapper">
                    <div class="tiles gradient-black p-l-20 p-r-20 p-b-20 p-t-20">
                      <h4 class="text-white semi-bold no-margin"><?php echo $curTrack->artists[0]->name; ?></h4>
                      <h5 class="text-white semi-bold "><?php echo $curTrack->name; ?></h5>
                    </div>
                  </div>
                </div>
                <img src="<?php echo $curTrack->album->images[0]->url; ?>" alt="<?php echo $curTrack->name; ?>" class="image-responsive-width xs-image-responsive-width"> </div>
            </div>
          </div>
          <?php } ?>
            <div class="col-md-8 col-vlg-4 col-sm-12 ">  
                <div class="grid simple ">                    
                    <div class="grid-body no-border">
                        <h3>Upcoming Tracks</h3>
                            <table class="table table-striped table-flip-scroll cf">
                                <thead class="cf">
                                    <tr>          
                                        <th>&nbsp;</th>                              
                                        <th>Artist</th>
                                        <th>Track</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach ($final as $track) {
                                        $item = $track->track;

                                            echo '<tr>';
                                            echo '<td><img src="'.$item->album->images[2]->url.'" width="24"/></td>';
                                            echo '<td>'.$item->name .'</td>';
                                            echo '<td>'.$item->artists[0]->name.'</td>';
                                            echo '<td><span class="badge">'.formatMilliseconds($item->duration_ms).'</span></td>';
                                            echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>
         </div>
       </div>
</div>

</body>
</html>
