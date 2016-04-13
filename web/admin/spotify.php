<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 0);

require '../../vendor/autoload.php';

Dotenv::load('../');

$spotifyUser = getenv('SPOTIFY_USER');
$maxSongs = getenv('MAX_SONGS');
$maxPlaylists = getenv('MAX_PLAYLISTS');
$masterPlaylist = getenv('MASTER_PLAYLIST');

$session = new SpotifyWebAPI\Session(
    getenv('SPOTIFY_CLIENT_ID'),
    getenv('SPOTIFY_CLIENT_SECRET'),
    'http://radio.tsr.local/admin/spotify.php');

$api = new SpotifyWebAPI\SpotifyWebAPI();

if (isset($_GET['code'])) {
    
    $session->requestToken($_GET['code']);
    $api->setAccessToken($session->getAccessToken());
    $refreshToken = $session->getRefreshToken();
    file_put_contents('refresh.token', $refreshToken);

} else {

  /*header('Location: ' . $session->getAuthorizeUrl(array(
        'scope' => array('playlist-read-private', 'user-read-private')
    )));
  exit;
*/
    $refreshToken = file_get_contents('refresh.token');

    $session->setRefreshToken($refreshToken);
    $session->refreshToken();

    $accessToken = $session->getAccessToken();
    $api->setAccessToken($accessToken);
    $refreshToken = $session->getRefreshToken();
    file_put_contents('refresh.token', $refreshToken);

}

$playlists = $api->getUserPlaylists($spotifyUser, array(
    'limit' => $maxPlaylists
));

$songs = array();
$finalList = array();

foreach ($playlists->items as $playlist) {

    if($playlist->id == $masterPlaylist) continue;

    try{
      $playlistTracks = $api->getUserPlaylistTracks($playlist->owner->id, $playlist->id);
    } catch(Exception $e){
        continue;
    }

    if(empty($playlistTracks)) continue;

      foreach ($playlistTracks->items as $track) {
        $track = $track->track;

        if($track->explicit) continue;

        $songs[] = $track->id;
      }

      if(empty($songs)) continue;

      if(count($songs) < $maxSongs){
            $limit = count($songs);
        } else {
            $limit = $maxSongs;
        }

      $keys = array_rand($songs, $limit);      

      foreach($keys as $key){
        $items[] = $songs[$key];
      }
      
      $finalList = array_merge($finalList, $items);
      unset($songs, $playlistTracks);
}

$finalList = array_unique($finalList);
shuffle($finalList);

if(!empty($finalList)){
    $api->replacePlaylistTracks($spotifyUser, $masterPlaylist, array($finalList[0]));
    unset($finalList[0]);

    if(!empty($finalList)){
        foreach($finalList as $key => $value){
          try{
            $api->addUserPlaylistTracks($spotifyUser, $masterPlaylist, array($value));
          } catch(SpotifyWebAPI\SpotifyWebAPIException $e){
            continue;
          }
        }
    }
}

$msg = array(
  'success'     => true,
  'tracksAdded' => count($finalList),
);

print_r(json_encode($msg));
die;
