<?php
/*
Plugin Name: SimpleTicker
Plugin URI: http://simpleticker.mbartel.de/
Description: Simple news ticker with multiple input possiblities
Version: 0.9
Author: Michael Bartel
Author URI: http://facebook.com/bartel.michael/
License: MIT/X11
*/

$simpleTickerVersion = "0.9";

/*
 * Include wordpress config and database files
 */
@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR. "wp-config.php");
@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR."wp-includes/wp-db.php");

$wp_version = get_bloginfo('version');

/*
 * If no ticker ID is given by the id GET parameter, try to get the ID with the name parameter
 */
$id_SimpelTicker = (isset($_GET['id']) && $_GET['id'] != '') ? $_GET['id'] : getSimpleTickerIDByName($_GET['name']);

/*
 * handle AJAX requests
 */
if ($_GET['action'] == 'getTickerDetails') {
    echo json_encode(getAJAXSimpleTickerDetails($id_SimpelTicker));
    die();
} else if ($_GET['action'] == 'getTickerMessages') {
    echo json_encode(getAJAXSimpleTickerMessages($id_SimpelTicker, $_GET['count'], $_GET['timeout']));
    die();
}
/*
 * Handle API JSON requests
 */
if ($_GET['action'] == 'jsonGetTickerList') {
    echo json_encode(getSimpleTickerList());
    die();
} else if ($_GET['action'] == 'jsonGetTickerMessages') {
    echo json_encode(getSimpleTickerMessages($id_SimpelTicker));
    die();
} else if ($_GET['action'] == 'getBlogName') {
    echo get_bloginfo('name');
    die();
} else if ($_GET['action'] == 'jsonManageTicker') {
    manageSimpleTicker($id_SimpelTicker, $_GET['data']);
}

/*
 * Handle RSS Feed
 */
if ($_GET['action'] == "rssFeed") {
    header("Content-Type: application/rss+xml");
    echo getSimpleTickerRSSFeed($id_SimpelTicker);
    die();
}

/*
 * Plugin installation
 */
register_activation_hook(__FILE__, 'installSimpleTicker');
function installSimpleTicker() {
    global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql = "CREATE TABLE " . $wpdb->prefix . "SimpleTicker (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `name` VARCHAR(100) NOT NULL,
          `updateInterval` INT(11) NOT NULL,
          `messageTimeout` INT(11) NOT NULL,
          `messageCount` INT(11) NOT NULL,
          `tickerTimeout` INT(11) NOT NULL,
          `passwd` VARCHAR(250) DEFAULT NULL,
          UNIQUE KEY id (id)
        );";
    
    dbDelta($sql);

    $sql = "CREATE TABLE `svlk_SimpleTickerMsgs` (
          `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `id_SimpleTicker` INT(11) DEFAULT NULL,
          `message` VARCHAR(250) DEFAULT NULL,
          `createdOn` DATETIME DEFAULT NULL,
          PRIMARY KEY (`id`)
        );";

    dbDelta($sql);
}

/*
 * add javascript and styles
 */
add_action('init', 'initSimpleTicker');
function initSimpleTicker() {
    wp_enqueue_script("jquery");

    $myStyleUrl = plugins_url('simpleticker.css', __FILE__);
    wp_register_style('simpleTickerStyleSheets', $myStyleUrl);
    wp_enqueue_style( 'simpleTickerStyleSheets');

    $myJavaScriptUrl = plugins_url('simpleticker.js', __FILE__);
    wp_register_script('simpleTickerJavaScripts', $myJavaScriptUrl);
    wp_enqueue_script( 'simpleTickerJavaScripts');
}

/*
 * provide plugins base URL to JavaScript for AJAX requests
 */
add_action('wp_head', 'simpleTickerBaseURL');
function simpleTickerBaseURL() {
    echo '<script type="text/javascript">var simpleTickerBaseURL = "' . plugins_url('', __FILE__) . '/";</script>';
}

/*
 * Uninstall plugin
 */
register_deactivation_hook(__FILE__, 'uninstallSimpleTicker');
function uninstallSimpleTicker() {
    global $wpdb;
    $wpdb->query("DROP TABLE " . $wpdb->prefix ."SimpleTicker");
    $wpdb->query("DROP TABLE " . $wpdb->prefix ."SimpleTickerMsgs");
}

/*
 * create the ticker code itself
 */
add_shortcode('simpleticker', 'printSimpleTicker');
function printSimpleTicker($atts) {
    // we need this to extract the id from [simpleticker id=0]
    extract(shortcode_atts(array('id'=>1), $atts));
    
    return '<div id="SimpleTicker' . $id . '" class="SimpleTicker"><span></span></div>';
}

/*
 * Register admin menu page in plugins submenu
 */
add_action('admin_menu', 'showSimpleTickerAdminPage');
function showSimpleTickerAdminPage() {
    add_submenu_page('plugins.php', __('SimpleTicker'), __('SimpleTicker'), 10, 'simpletickeradmin', 'printSimpleTickerAdminPage');
}
function printSimpleTickerAdminPage() {
    global $wpdb;

    /*
     * update the list of tickers
     */
    if (count($_POST['ticker']) > 0) {
        $tickers = $_POST['ticker'];
        foreach ($tickers as $tickerId => $ticker) {
            if ($tickerId == 'new') {
                if ($ticker['name'] != '') {
                    if ($ticker['passwd'] == '') {
                        $query = "INSERT INTO  " . $wpdb->prefix ."SimpleTicker (name, updateInterval, messageTimeout, messageCount, tickerTimeout) " .
                                "VALUES('" . $ticker['name'] . "', '" . $ticker['updateInterval'] . "', '" . $ticker['messageTimeout'] . "', '" .
                                $ticker['messageCount'] . "', '" . $ticker['tickerTimeout'] . "')";
                    } else {
                        $query = "INSERT INTO  " . $wpdb->prefix ."SimpleTicker (name, updateInterval, messageTimeout, messageCount, tickerTimeout, passwd) " .
                                "VALUES('" . $ticker['name'] . "', '" . $ticker['updateInterval'] . "', '" . $ticker['messageTimeout'] . "', '" .
                                $ticker['messageCount'] . "', '" . $ticker['tickerTimeout'] . "', '" . md5($ticker['passwd']) . "')";
                    }
                    $wpdb->query($query);
                }
                unset($tickers[$tickerId]);
                continue;
            }
            if ($ticker['passwd'] == '') {
                $wpdb->query("UPDATE " . $wpdb->prefix ."SimpleTicker SET name='" . $ticker['name'] . "', updateInterval='" . $ticker['updateInterval'] .
                    "', messageTimeout='" . $ticker['messageTimeout'] . "', messageCount='" . $ticker['messageCount'] . "', tickerTimeout='" . $ticker['tickerTimeout'] .
                    "' WHERE id='$tickerId'");
            } else {
                $wpdb->query("UPDATE " . $wpdb->prefix ."SimpleTicker SET name='" . $ticker['name'] . "', updateInterval='" . $ticker['updateInterval'] .
                    "', messageTimeout='" . $ticker['messageTimeout'] . "', messageCount='" . $ticker['messageCount'] . "', tickerTimeout='" . $ticker['tickerTimeout'] .
                    "', passwd='" . md5($ticker['passwd']) . "' WHERE id='$tickerId'");
            }

            if ($ticker['delete'] == "1") {
                $wpdb->query("DELETE FROM " . $wpdb->prefix ."SimpleTicker WHERE id='$tickerId'");
                $wpdb->query("DELETE FROM " . $wpdb->prefix ."SimpleTickerMsgs WHERE id_SimpleTicker='$tickerId'");
            }
        }
    }
    
    /*
     * update the list of messages
     */
    if (count($_POST['msg']) > 0) {
        $msgs = $_POST['msg'];
        foreach ($msgs as $msgId => $msg) {
            if ($msgId == 'new') {
                if ($msg['message'] != '') {
                    $query = "INSERT INTO  " . $wpdb->prefix ."SimpleTickerMsgs (id_SimpleTicker, message, createdOn) " .
                                "VALUES('" . $msg['ticker'] . "', '" . $msg['message'] . "', NOW())";
                    $wpdb->query($query);
                }
            } elseif ($msg['delete'] == "1") {
                $wpdb->query("DELETE FROM " . $wpdb->prefix ."SimpleTickerMsgs WHERE id='$msgId'");
            }
        }
    }

    /*
     * print the list of tickers
     */
    echo '<form action="#" method="post">
          <table width="100%">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Ticker updates (every x minutes)</th>
                <th>Message fades (after x secondes)</th>
                <th>Number of fading messages</th>
                <th>Show no messages created x minutes ago</th>
                <th>Password</th>
                <th>Delete</th>
                <th>Copy</th>
              </tr>
            </thead>
            <tbody>';

    $pluginpath = plugins_url('', __FILE__);
    $tickers = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "SimpleTicker");
    $tickerList = array();
    foreach ($tickers as $ticker) {
        $tickerList[$ticker->id] = $ticker->name;
        $id = $ticker->id;

        echo '<tr>
                <td align="center">' . $id . '</td>
                <td align="center"><input type="text" name="ticker[' . $id . '][name]" value="' . $ticker->name . '" /></td>
                <td align="center"><input type="text" name="ticker[' . $id . '][updateInterval]" value="' . $ticker->updateInterval . '" /></td>
                <td align="center"><input type="text" name="ticker[' . $id . '][messageTimeout]" value="' . $ticker->messageTimeout . '" /></td>
                <td align="center"><input type="text" name="ticker[' . $id . '][messageCount]" value="' . $ticker->messageCount . '" /></td>
                <td align="center"><input type="text" name="ticker[' . $id . '][tickerTimeout]" value="' . $ticker->tickerTimeout . '" /></td>
                <td align="center"><input type="text" name="ticker[' . $id . '][passwd]" value="" /></td>
                <td align="center"><input type="checkbox" name="ticker[' . $id . '][delete]" value="1" /></td>
                <td align="center">
                  <a href="javascript: window.alert(\'RSS Feed URL:\n\n' . 
                    $pluginpath . '/simpleticker.php?action=rssFeed&name=' . $ticker->name . ' \n\nor\n\n' . 
                    $pluginpath . '/simpleticker.php?action=rssFeed&name=' . $id . '\');">RSS</a>
                </td>
              </tr>';
    }

    echo'  </tbody>
           <tfooter>
             <tr>
               <td></td>
               <td align="center"><input type="text" name="ticker[new][name]"/></td>
               <td align="center"><input type="text" name="ticker[new][updateInterval]"/></td>
               <td align="center"><input type="text" name="ticker[new][messageTimeout]"/></td>
               <td align="center"><input type="text" name="ticker[new][messageCount]"/></td>
               <td align="center"><input type="text" name="ticker[new][tickerTimeout]"/></td>
               <td align="center"><input type="text" name="ticker[new][passwd]"/></td>
               <td colspan="2"></td>
             </tr>
           </tfooter>
         </table>
         <p align="center"><input type="submit" value="Update" /></p></form>

         <form action="#" method="post">
         <table width="100%">
           <thead>
             <tr>
               <th>Ticker</th>
               <th>Message</th>
               <th>Created</th>
               <th>Delete</th>
             </tr>
           </thead>
           <tbody>';

    $messages = $wpdb->get_results("SELECT " . $wpdb->prefix . "SimpleTickerMsgs.id AS tickerId, id_SimpleTicker, name, message, createdOn FROM " .
                                        $wpdb->prefix . "SimpleTickerMsgs JOIN " .
                                        $wpdb->prefix . "SimpleTicker ON " . $wpdb->prefix . "SimpleTickerMsgs.id_SimpleTicker = " .
                                        $wpdb->prefix . "SimpleTicker.id ORDER BY createdOn DESC LIMIT 50");

    foreach ($messages as $message) {
        echo '<tr>
                <td align="center">' . $message->name . '</td>
                <td align="left">' . $message->message . '</td>
                <td align="center">' . $message->createdOn . '</td>
                <td align="center"><input type="checkbox" name="msg[' . $message->tickerId . '][delete]" value="1" /></td>
            </tr>';
    }


    echo '</tbody>
          <tfooter>
            <tr><td colspan="4">&nbsp;</td></tr>
            <tr>
              <td align="center">
                <select name="msg[new][ticker]">';

    foreach ($tickerList as $key => $value) {
        echo '<option value="' . $key . '">' . $value . '</option>';
    }

    echo '      </select>
              </td>
              <td align="center"><input type="text" name="msg[new][message]" size="100"/></td>
              <td align="left"><input type="submit" value="Create new message / delete selected messages"/></td>
              <td></td>
            </tr>
          </tfooter>
        </table>
      </form>';
}

/**
 * Returns the decrypted string
 * @param <string> $str
 * @return <string>
 */
function decryptSimpleTickerMessage($str) {
    return $str; //TODO implement decryption
}

/**
 * Returns the ticker ID by it's name
 * @param <type> $name 
 */
function getSimpleTickerIDByName($name) {
    global $wpdb;
    $query = "SELECT id FROM " . $wpdb->prefix . "SimpleTicker WHERE name='$name'";
    $row = $wpdb->get_Row($query);
    return $row->id;
}


/**
 * Return detailed information about a specific ticker (defined by it's ID)
 * @global <type> $wpdb
 * @param <type> $id_SimpleTicker
 * @return <type>
 */
function getAJAXSimpleTickerDetails($id_SimpleTicker) {
    global $wpdb;
    $query = "SELECT name, updateInterval, messageTimeout, messageCount, tickerTimeout FROM " . $wpdb->prefix . "SimpleTicker WHERE id='" . mysql_escape_string($id_SimpleTicker) . "'";
    $queryResult = $wpdb->get_row($query);

    $result = array();
    $result['updateInterval'] = $queryResult->updateInterval * 60;
    $result['messageTimeout'] = $queryResult->messageTimeout;
    $result['messageCount'] = $queryResult->messageCount;
    $result['tickerTimeout'] = $queryResult->tickerTimeout * 60;
    $result['name'] = $queryResult->name;

    return $result;
}

/**
 * Return all currently 'active' messages (messages, where the timeout hasn't been exceeded) for a ticker with a specific ID
 * @global <type> $wpdb
 * @param <type> $id_SimpleTicker
 * @param <type> $messageCount
 * @param <type> $messageTimeout
 */
function getAJAXSimpleTickerMessages($id_SimpleTicker, $messageCount, $messageTimeout) {
    global $wpdb;
    $query = "SELECT message FROM " . $wpdb->prefix . "SimpleTickerMsgs WHERE " .
            "id_SimpleTicker='" . mysql_escape_string($id_SimpleTicker) . "' AND createdOn > '" . date("Y-m-d h:i:s", time() - mysql_escape_string($messageTimeout) * 60) . "' " .
            "ORDER BY createdOn DESC LIMIT " . mysql_escape_string($messageCount);
    $messages = $wpdb->get_results($query);

    $result = array();
    foreach ($messages as $message) {
        $result[] = $message->message;
    }

    return $result;
}

/**
 * Returns a list of all ticker (ID and name)
 * @global  $wpdb
 * @return <type>
 */
function getSimpleTickerList() {
    global $wpdb;
    $tickers = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "SimpleTicker");
    $tickerList = array();
    foreach ($tickers as $ticker) {
        $tickerList[] = array('id' => $ticker->id, 'name' => $ticker->name);
    }
    return $tickerList;
}

/**
 * Returns a list with the 50 latest messages for the ticker with the given ticker ID
 * @global $wpdb $wpdb
 * @param <type> $id_SimpleTicker
 * @return <type>
 */
function getSimpleTickerMessages($id_SimpleTicker) {
    global $wpdb;
    $messages = $wpdb->get_results("SELECT id, message, createdOn FROM " .
                                        $wpdb->prefix . "SimpleTickerMsgs WHERE id_SimpleTicker = '" . mysql_escape_string($id_SimpleTicker) .
                                        "' ORDER BY createdOn DESC LIMIT 50");
    $tickerMessages = array();
    foreach ($messages as $message) {
        $uid = substr(str_pad(str_replace(array(' ', ':', '-'), array('', '', ''), $id_SimpleTicker . $message->id . $message->createdOn. $message->message), 32, 'A'), 0, 32);
        $tickerMessages[] = array('id' => $message->id, 'message' => $message->message, 'createdOn' => $message->createdOn, 'uid' => $uid);
    }

    return $tickerMessages;
}

/**
 * Manage tickers with JSON request and encrypted BASE64 data
 * @global $wpdb;
 * @param <type> $id_SimpleTicker
 * @param <type> $data 
 */
function manageSimpleTicker($id_SimpleTicker, $data) {
    global $wpdb;
    $id_SimpleTicker = mysql_escape_string($id_SimpleTicker);

    /*
     * Check if the password encrypted with the token matches the password for this ticker stored in the database.
     */
    $decryptedData = json_decode(decryptSimpleTickerMessage(base64_decode($data)), true);
    if ($decryptedData == null) {
        die("NODATACONTENT");
    }
    $tickerData = $wpdb->get_row("SELECT passwd FROM " . $wpdb->prefix . "SimpleTicker WHERE id='" . mysql_escape_string($id_SimpleTicker) . "'");
    if (md5($decryptedData['passwd']) == $tickerData->passwd) {
        /*
         * Add new messsage
         */
        if ($decryptedData['action'] == 'addMessage') {
            $message = $decryptedData['message'];
            $wpdb->query("INSERT INTO " . $wpdb->prefix . "SimpleTickerMsgs (id_SimpleTicker, message, createdOn) VALUES ('$id_SimpleTicker', '$message', NOW())");
            die("SUCCESS");
        }
        /*
         * Delete existing message
         */
        if ($decryptedData['action'] == 'removeMessage') {
            $id = $decryptedData['id'];
            $wpdb->query("DELETE FROM " . $wpdb->prefix . "SimpleTickerMsgs WHERE id='$id'");
            die("SUCCESS");
        }
        die("NOACTIONDEFINED");
    } else {
        die("WRONGPASSWORD");
    }
}

/**
 * Create string content for the RSS Feed
 * @param <type> $id_SimpleTicker
 */
function getSimpleTickerRSSFeed($id_SimpleTicker) {
    $tickerInfo = getAJAXSimpleTickerDetails($id_SimpleTicker);
    $messages = getSimpleTickerMessages($id_SimpleTicker);

    $output =  '<?xml version="1.0" encoding="UTF-8" ?>';
    $output .= '<rss version="2.0">';
    $output .= '<channel>';
    $output .= '  <title>' . $tickerInfo['name'] . '</title>';
    $output .= '  <description>Wordpress Simple Ticker RSS Feed</description>';
    $output .= '  <lastBuildDate>' . date('r') . '</lastBuildDate>';
    $output .= '  <pubDate>' . $messages[0]['createdOn'] . '</pubDate>';
    foreach ($messages as $message) {
        $output .= '  <item>';
        $output .= '    <title>' . $message['message'] . '</title>';
        $output .= '    <guid>' . $message['uid'] . '</guid>';
        $output .= '    <pubDate>' . $message['createdOn'] . '</pubDate>';
        $output .= '  </item>';
    }
    $output .= '</channel>';
    $output .= '</rss>';
    return $output;
}

?>
