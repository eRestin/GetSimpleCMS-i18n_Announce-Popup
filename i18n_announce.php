<?php
/*
Plugin Name: i18n_announce
Description: Allows for time-based announcements
Version: 0.1
Author: Aschwin van der Woude <aschwin vanderwoude info>
Author URI: 

License: GPL version 3

 This file is part of i18n_announce plugin for GetSimple.

 The i18n_annouce plugin is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 The i18_announce plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
*/

# get correct id for plugin
$this_plugin=basename(__FILE__, ".php");
$ANNOUNCE_FILE = GSDATAOTHERPATH . 'i18n_announcements.txt';
$ANNOUNCE_TEST_DATA = array();

## Add test data
$ANNOUNCE_TEST_DATA[0] = array("start" => strtotime("20140401"), "end" => strtotime("20140410"), "title" => array("en" =>"Fun event", "fi" => "Hauskaa tapahtuma", "ja" => "Tanoshii ebento"), "description" => array("en" => "Doing something fun", "fi" => "Tehdä jotain hauskaa", "ja" => "tanoshite"));
$ANNOUNCE_TEST_DATA[1] = array("start" => strtotime("20140501"), "end" => strtotime("20140513 23:59"), "title" => array("en" => "Japanese event", "fi" => "Japanilainen tapahtumaa"), "description" => array("en" => "Eating Japanese food", "fi" => "Syödä japanilainen ruoka", "ja" => "Nihonno tabemonoga tabemasu"));

# register plugin
register_plugin(
    $this_plugin,                           # Plugin id
    'i18n Announce',                        # Plugin Name
    '0.1',                                  # Plugin version
    'Aschwin van der Woude',                # Plugin author
    '',                                     # Author website
    'Provides time-based announcements',    # Plugin description
    'pages',                                # Page type - on which admin tab to display
    'i18n_announce_main'                    # Main function (administration)
);

# Plugin setup
add_action('pages-sidebar', 'createSideMenu', array($this_plugin,'Announcements'));
add_action('index-pretemplate', 'i18n_announce_index_pretemplate_hook', array());
register_style('i18n_announce-style', $SITEURL.'plugins/i18n_announce/css/i18n_announce.css', '1.0', 'screen');
queue_style('i18n_announce-style', GSBACK); 
register_script('i18n_announce', $SITEURL . 'plugins/i18n_announce/js/i18n_announce.js', "0.1", FALSE);
queue_script('i18n_announce', GSBACK);

# Filter
add_filter('content', 'filter_anouncements');

# Jquery
register_script('i18n_announce_jquery', $SITEURL . 'plugins/i18n_announce/jquery-ui/js/jquery-1.10.2.js', '1.10.2', FALSE);
register_script('i18n_announce_jquery_ui', $SITEURL . 'plugins/i18n_announce/jquery-ui/js/jquery-ui-1.10.4.custom.min.js', '1.10.4', FALSE);
queue_script('i18n_announce_jquery', GSBACK);
queue_script('i18n_announce_jquery_ui', GSBACK);
register_style('i18n_announce_jquery_ui_style', $SITEURL . 'plugins/i18n_announce/jquery-ui/css/ui-lightness/jquery-ui-1.10.4.custom.min.css', '1.0', 'screen');
queue_style('i18n_announce_jquery_ui_style', GSBACK);


# functions
function i18n_announce_index_pretemplate_hook() {
    i18n_init();
}

function read_announcements() {
    global $ANNOUNCE_FILE;
    return file_exists($ANNOUNCE_FILE)?@unserialize(@file_get_contents($ANNOUNCE_FILE)):array();
}

function write_announcements($data) {
    global $ANNOUNCE_FILE;
    file_put_contents($ANNOUNCE_FILE, serialize($data));
}

function equalise_languages($announcements) {
    # Ensure all site-wide configured languages are present
    $languages = return_i18n_available_languages();
    foreach ($announcements as &$ann) {
        foreach ($languages as $lang) {
            if (!array_key_exists($lang, $ann['title'])) {
                $ann['title'][$lang] = "";
            }
            if (!array_key_exists($lang, $ann['description'])) {
                $ann['description'][$lang] = "";
            }
        }
    }
    return $announcements;
}

function unix2js_timestamp($val) {
    return $val * 1000;
}

function js2unix_timestamp($val) {
    return $val / 1000;
}

function return_announcements() {
    $announcements = read_announcements();
    $announcements = equalise_languages($announcements);
    // TODO: Ensure all current languages are returned
    return $announcements;
}

function is_current_announcement($announcement) {
    $today = time();
    if ($announcement['start'] < $today && $today < $announcement['end']) {
        return TRUE;
    }
    return FALSE;
}

function get_announcement_field($announcement, $field) {
    $req_languages = return_i18n_languages(); // User requested languages in order of preference
    foreach ($req_languages as $lang) {
        if (isset($announcement[$field][$lang]) and $announcement[$field][$lang] != "") {
            return $announcement[$field][$lang]; #Only show the first available from the requested languages
        }
    }
}

function get_current_announcements() {
    $announcements = return_announcements();
    foreach ($announcements as $ann) {
        if (is_current_announcement($ann)) {
            echo get_announcement_field($ann, 'title') . "<br/>";
        }
    }
}

function current_announcement_exists() {
    $announcements = return_announcements();
    foreach ($announcements as $ann) {
        if (is_current_announcement($ann)) {
            return TRUE;
        }
    }
    return FALSE;
}

function get_announcement_languages($announcement) {
    $languages = array();
    foreach (array_keys($announcement['title']) as $lang) {
        if (!in_array($lang, $languages)) {
            $languages[] = $lang;
        }
    }
    foreach (array_keys($announcement['description']) as $lang) {
        if (!in_array($lang, $languages)) {
            $languages[] = $lang;
        }
    }
    return $languages;
}

function get_field_name($id, $field, $lang="") {
    if ($lang == "") {
        return "announcements[" . $id . "][" . $field . "]";
    } else {
        return "announcements[" . $id . "][" . $field . "][" . $lang . "]";
    }
}

function print_empty_announcement($id) {
    global $SITEURL;

    echo "<div class=\"i18n_announcement\">";
    echo "<div class=\"announcement-date\">";
    echo "Period shown: ";
    echo '<span id="ann-start-label-' . $id . '">&lt;Please select start date&gt;</span>';
    echo '<input type="hidden" class="date" data-label="ann-start-label-' . $id . '" name="' . get_field_name($id, "start") . '"/>';
    echo "&nbsp;-&nbsp;";
    echo '<span id="ann-end-label-' . $id . '">&lt;Please select end date&gt;</span>';
    echo '<input type="hidden" class="date" data-label="ann-end-label-' . $id . '" name="' . get_field_name($id, "end") . '"/>';
    echo "</div>";
    echo "<table>";
    echo "<tr><th>Language</th><th>Title</th><th>Description</th></tr>";
    foreach (return_i18n_available_languages() as $lang) {
        echo "<tr>";
        echo "<td><b>" . $lang . "</b></td>";
        echo '<td><input type="text" name="' . get_field_name($id, "title", $lang) . ']"/></td>';
        echo '<td><input type="text" name="' . get_field_name($id, "description", $lang) . ']"/></td>';
        echo "</tr>";
    }
    echo "</table>";
    echo "</div> <!-- i18n_announcement -->";
}

function print_announcement($id, $ann) {
    global $SITEURL;
    echo "<div class=\"i18n_announcement\">";
    echo "<div class=\"announcement-date\">";
    echo "Period shown: ";
    echo '<span id="ann-start-label-' . $id . '">' . date("d F Y", $ann['start']) . '</span>';
    echo '<input type="hidden" data-label="ann-start-label-' . $id . '" class="date" name="' . get_field_name($id, "start") . '" value="'. unix2js_timestamp($ann['start']) . '" />';
    echo "&nbsp;-&nbsp;";
    echo '<span id="ann-end-label-' . $id . '">' . date("d F Y", $ann['end']) . '</span>';
    echo '<input type="hidden" data-label="ann-end-label-' . $id . '" class="date" name="' . get_field_name($id, "end") . '" value="'. unix2js_timestamp($ann['end']) . '" />';
    echo "</div>";
    echo "<table>";
    echo "<tr><th>Language</th><th>Title</th><th>Description</th></tr>";
    foreach (get_announcement_languages($ann) as $lang) {
        echo "<tr>";
        echo "<td><b>" . $lang . "</b></td>";
        echo '<td><input type="text" name="' . get_field_name($id, 'title', $lang) . '" size="50" value="' . $ann['title'][$lang] . '"/></td>';
        echo '<td><input type="text" name="' . get_field_name($id, 'description', $lang) . '" value="' . $ann['description'][$lang] . '"/></td>';
        echo "</tr>";
    }
    echo "</table>";
    echo "</div> <!-- i18n_announcement -->";
}

function i18n_print_header($current_tab = "active") {
    echo '<h3 class="floated" style="float:left">Announcements</h3>';
    echo '<div class="edit-nav">';
    if ($current_tab == "active") {
        echo ' <a href="load.php?id=i18n_announce&tab=old">Old</a>';
        echo ' <a href="load.php?id=i18n_announce&tab=active" class="current">Active</a>';
    } else {
        echo ' <a href="load.php?id=i18n_announce&tab=old" class="current">Old</a>';
        echo ' <a href="load.php?id=i18n_announce&tab=active">Active</a>';
    }
    echo ' <div class="clear"></div>';
    echo '</div>';
    echo '<form method="POST">';
}

function i18n_print_footer() {
    echo '<input class="submit" type="submit" name="save_announcements" value="Save Announcements">';
    echo '</form>';
}

function i18n_announce_show_active() {
    $announcements = return_announcements();
    i18n_print_header("active");
    $today = time();
    foreach (array_keys($announcements) as $id) {
        if ($today < $announcements[$id]['end']) {
            print_announcement($id, $announcements[$id]);
        }
    }
    $new_id = count($announcements);
    print_empty_announcement($new_id);
    i18n_print_footer();
}

function i18n_announce_show_old() {
    $announcements = return_announcements();
    i18n_print_header("old");
    $today = time();
    $count = 0;
    foreach (array_keys($announcements) as $id) {
        if ($today > $announcements[$id]['end']) {
            print_announcement($id, $announcements[$id]);
            $count++;
        }
    }
    if ($count == 0) {
        echo "There are no old announcements.";
    }
    i18n_print_footer();
}

function sanitise_timestamps($new_announcements) {
    // This function can only be used on values coming from the frontend
    // as it is assumed that the timestamps are in milliseconds, which is the JS default
    foreach ($new_announcements as &$ann) {
        if ($ann['start'] != "") {
            $ann['start'] = js2unix_timestamp($ann['start']);
        } else {
            $ann['start'] = time();
        }
        if ($ann['end'] != "") {
            $ann['end'] = js2unix_timestamp($ann['end']);
        } else {
            $ann['end'] = time();
        }
        if ($ann['start'] > $ann['end']) {
            $val = $ann['start'];
            $ann['start'] = $ann['end'];
            $ann['end'] = $val;
        }
    }
    return $new_announcements;
}

function remove_empty_announcements($announcements) {
    $clean_announcements = array();

    foreach ($announcements as $ann) {
        $count = 0;
        foreach ($ann["title"] as $title) {
            if ($title != "") {
                $count++;
            }
        }
        foreach ($ann["description"] as $desc) {
            if ($desc != "") {
                $count++;
            }
        }
        if ($count > 0) {
            $clean_announcements[] = $ann;
        }
    }
    return $clean_announcements;
}

function filter_anouncements($content) {
    $announcements = return_announcements();
    $html = "";
    foreach($announcements as $ann) {
        $html .= '<h2>' . get_announcement_field($ann, 'title') . '</h2>';
        $desc = get_announcement_field($ann, 'description');
        if ($desc == "") {
            $desc = "&lt;No description&gt;";
        }
        $html .= '<div>' . $desc  . '</div>';
    }
    $content = str_replace('(%announcement-list%)', $html, $content);
    return $content;
}

function i18n_announce_main() {
    global $ANNOUNCE_TEST_DATA;
//    write_announcements($ANNOUNCE_TEST_DATA);
    if (isset($_POST['save_announcements'])) {
        $old_announcements = return_announcements();
        $new_announcements = $_POST["announcements"];
        $new_announcements = sanitise_timestamps($new_announcements);
        $announcements = array_replace($old_announcements, $new_announcements);
        $announcements = remove_empty_announcements($announcements);
        write_announcements($announcements);
        echo '<div id="alert_message">Announcements saved</div>';
    }
    if (isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'old') {
        i18n_announce_show_old();
    } else {
        i18n_announce_show_active();
    }
}

?>
