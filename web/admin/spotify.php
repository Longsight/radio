<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 0);

require '../../vendor/autoload.php';

Dotenv::load('../');

$spotifyUser = getenv('SPOTIFY_USER');
$maxPlaylists = getenv('MAX_PLAYLISTS');
$masterPlaylist = getenv('MASTER_PLAYLIST');
$fillTime = getenv('FILL_TIME');

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

$playlists = $api->getUserPlaylists($spotifyUser,
    array(
        'limit' => $maxPlaylists
    ));

$fullList = array();
$fullDuration = 0;
ini_set('display_errors', 1);

foreach ($playlists->items as $playlist) {
    $duration = 0;
    $songs = array();

    if ($playlist->id == $masterPlaylist) {
        continue;
    }

    try {
        $playlistTracks = $api->getUserPlaylistTracks($playlist->owner->id, $playlist->id);
    } catch (Exception $e) {
        continue;
    }

    if (empty($playlistTracks)) {
        continue;
    }

    foreach ($playlistTracks->items as $track) {
        $track = $track->track;

        if ($track->explicit) {
            continue;
        }

        $songs[] = $track;
        $duration += ($track->duration_ms / 1000);
    }

    if (empty($songs)) {
        continue;
    }
    shuffle($songs);

    $fullList[] = [
        'duration' => $duration,
        'tracks' => $songs
    ];
    $fullDuration += $duration;
}

$finalList = array();

foreach ($fullList as $playlist) {
    $avgTrack = $playlist['duration'] / count($playlist['tracks']);
    $listTime = $playlist['duration'] * min(($fillTime / $fullDuration), 1);
    $grabTracks = ceil($listTime / $avgTrack);
    $selected = array_slice($playlist['tracks'], 0, $grabTracks);
    $finalList = array_merge($finalList, $selected);
}

$finalList = array_unique($finalList, SORT_REGULAR);
shuffle($finalList);

if (!empty($finalList)) {
    $api->replacePlaylistTracks($spotifyUser, $masterPlaylist, array($finalList[0]));
    unset($finalList[0]);

    if (!empty($finalList)) {
        foreach ($finalList as $key => $value) {
            try {
                $api->addUserPlaylistTracks($spotifyUser, $masterPlaylist, array($value));
            } catch (SpotifyWebAPI\SpotifyWebAPIException $e) {
                continue;
            }
        }
    }
}

$msg = array(
    'success' => true,
    'tracksAdded' => count($finalList),
);

print_r(json_encode($msg));
die;
