<?php

require_once 'DBAccess.php';
require_once 'DateManager.php';
require_once 'utils.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
setlocale(LC_ALL, 'it_IT');

session_start();
$isLoggedIn = isset($_SESSION['logged_id']);
$loginOrProfileTitle = $isLoggedIn ?
    "<a href=\"artista.php?id=" . $_SESSION['logged_id'] . "\"><span lang=\"en\">Account</span></a>" :
    "<a href=\"login.php\">Accedi</a>";

$connection = new DB\DBAccess();
if (!$connection->openDBConnection()) {
    header("location: ../php/500.php");
    exit();
}

$titleSearch = isset($_GET["title"]) ? $_GET["title"] : "";
$dateSearch = isset($_GET["date"]) ? $_GET["date"] : "";
$artshowSearch = isset($_GET["artshow"]) ? $_GET["artshow"] : "";
$labelSearch = array();
$labels = $connection->getLabels();

$labelsContainer = '';
if ($labels && sizeof($labels) > 0) {
    $labelsContainer = "<ul id=\"labels_list\">";
    foreach ($labels as $label) {
        $labelChecked = isset($_GET[$label['label']]) ? "checked" : "";
        $labelsContainer .= "
        <li>
            <input
                type=\"checkbox\"
                class=\"label_checkbox\"
                id=\"" . $label['label'] . "\"
                value=\"" . $label['label'] . "\"
                name=\"" . $label['label'] . "\" 
                " . $labelChecked . ">
            <label for=\"" . $label['label'] . "\">" . ucfirst($label['label']) . "</label>
        </li>";

        if (isset($_GET[$label['label']])) array_push($labelSearch, $_GET[$label['label']]);
    }
    $labelsContainer .= "</ul>";
}


$artists = $connection->getArtistQuery($titleSearch, $dateSearch, $artshowSearch, $labelSearch);

$connection->closeConnection();

$artshowChecked = $artshowSearch == "on" ? "checked" : "";

$artistContainer = "";
if ($artists and sizeof($artists) > 0) {
    $artistContainer = "<div class=\"artist_results_section\" id=\"paginated_section\">";
    foreach ($artists as $artist) {
        $id = $artist['id'];
        $username = $artist['username'];
        $name = $artist['name'];
        $lastname = $artist['lastname'];
        $profileImage = $artist['image'] ? $artist['image'] : '../assets/images/default_user.svg';
        $artistContainer .= "
            <div class=\"gallery_item\">
                <div class=\"artist_gallery_item\">
                    <div class=\"artist_gallery_item_image\">
                        <a
                            href=\"artista.php?id=" . $id . "\">
                            <img src=\"" . $profileImage . "\" alt=\"" . $name . " " . $lastname . "\">
                        </a>
                    </div>
                    <div class=\"artist_gallery_item_info\">
                        <div class=\"artist_gallery_item_title\">
                            <p title=\"" . $name . " " . $lastname . "\">" . $name . " " . $lastname . "</p>
                        </div>
                        <div class=\"artist_mini_preview_info\">
                            <a
                            aria-hidden=\"true\"
                            tabindex=\"-1\"
                            href=\"artista.php?id=" . $id . "\">" . $username . "</a>
                        </div>
                    </div>
                </div>
            </div>";
    }
    $artistContainer .= "</div>" . addPaginator();
}

$dateSearch = $dateSearch ? "value = \"$dateSearch\"" : "";

$artisti = file_get_contents("../templates/artisti.html");
$artisti = str_replace("{{login_or_profile_title}}", $loginOrProfileTitle, $artisti);
$artisti = str_replace("{{title}}", $titleSearch, $artisti);
$artisti = str_replace("{{date}}", $dateSearch, $artisti);
$artisti = str_replace("{{artshowChecked}}", $artshowChecked, $artisti);
$artisti = str_replace("{{labels}}", $labelsContainer, $artisti);
$artisti = str_replace("{{count}}", $artists ? sizeof($artists) : 0, $artisti);
$artisti = str_replace("{{results}}", $artistContainer, $artisti);
echo ($artisti);
