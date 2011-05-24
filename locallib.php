<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Private webctimport module utility functions
 *
 * @package    mod
 * @subpackage webctimport
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/webctimport/lib.php");

require_once("$CFG->dirroot/mod/url/locallib.php");


/**
 * Return full url with all extra parameters
 * @param string $url
 * @param object $cm
 * @param object $course
 * @return string url
 */
function webctimport_get_full_url($url, $cm, $course, $config=null) {

    $parameters = empty($url->parameters) ? array() : unserialize($url->parameters);

    if (empty($parameters)) {
        // easy - no params
        return $url->externalurl;
    }

    if (!$config) {
        $config = get_config('url');
    }
    $paramvalues = url_get_variable_values($url, $cm, $course, $config);

    foreach ($parameters as $parse=>$parameter) {
        if (isset($paramvalues[$parameter])) {
            $parameters[$parse] = urlencode($parse).'='.urlencode($paramvalues[$parameter]);
        } else {
            unset($parameters[$parse]);
        }
    }

    if (empty($parameters)) {
        // easy - no params available
        return $url->externalurl;
    }

    if (stripos($url->externalurl, 'teamspeak://') === 0) {
        return $url->externalurl.'?'.implode('?', $parameters);
    } else {
        $join = (strpos($url->externalurl, '?') === false) ? '?' : '&amp;';
        return $url->externalurl.$join.implode('&amp;', $parameters);
    }
}

/**
 * Print url header.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @return void
 */
function webctimport_print_header($url, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$url->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($url);
    echo $OUTPUT->header();
}

/**
 * Print url heading.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function webctimport_print_heading($url, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions);

    if ($ignoresettings or !empty($options['printheading'])) {
        echo $OUTPUT->heading(format_string($url->name), 2, 'main', 'urlheading');
    }
}

/**
 * Print url introduction.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function webctimport_print_intro($url, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($url->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'urlintro');
            echo format_module_intro('url', $url, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Display url frames.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function webctimport_display_frame($url, $cm, $course) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        url_print_header($url, $cm, $course);
        url_print_heading($url, $cm, $course);
        url_print_intro($url, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('url');
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        $exteurl = url_get_full_url($url, $cm, $course, $config);
        $navurl = "$CFG->wwwroot/mod/url/view.php?id=$cm->id&amp;frameset=top";
        $title = strip_tags(format_string($course->shortname.': '.$url->name));
        $framesize = $config->framesize;
        $modulename = s(get_string('modulename','url'));
        $dir = get_string('thisdirection', 'langconfig');

        $extframe = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navurl" title="$modulename"/>
    <frame src="$exteurl" title="$modulename"/>
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $extframe;
        die;
    }
}

/**
 * Print url info and link.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function webctimport_print_workaround($url, $cm, $course) {
    global $OUTPUT;

    url_print_header($url, $cm, $course);
    url_print_heading($url, $cm, $course, true);
    url_print_intro($url, $cm, $course, true);

    $fullurl = url_get_full_url($url, $cm, $course);

    $display = url_get_final_display_type($url);
    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$fullurl', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="urlworkaround">';
    print_string('clicktoopen', 'url', "<a href=\"$fullurl\" $extra>$fullurl</a>");
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}

/**
 * Display embedded url file.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function webctimport_display_embed($url, $cm, $course) {
    global $CFG, $PAGE, $OUTPUT;

    $mimetype = resourcelib_guess_url_mimetype($url->externalurl);
    $fullurl  = url_get_full_url($url, $cm, $course);
    $title    = $url->name;

    $link = html_writer::tag('a', $fullurl, array('href'=>str_replace('&amp;', '&', $fullurl)));
    $clicktoopen = get_string('clicktoopen', 'url', $link);

    $extension = resourcelib_get_extension($url->externalurl);

    if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {  // It's an image
        $code = resourcelib_embed_image($fullurl, $title);

    } else if ($mimetype == 'audio/mp3') {
        // MP3 audio file
        $code = resourcelib_embed_mp3($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/x-flv' or $extension === 'f4v') {
        // Flash video file
        $code = resourcelib_embed_flashvideo($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'application/x-shockwave-flash') {
        // Flash file
        $code = resourcelib_embed_flash($fullurl, $title, $clicktoopen);

    } else if (substr($mimetype, 0, 10) == 'video/x-ms') {
        // Windows Media Player file
        $code = resourcelib_embed_mediaplayer($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/quicktime') {
        // Quicktime file
        $code = resourcelib_embed_quicktime($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/mpeg') {
        // Mpeg file
        $code = resourcelib_embed_mpeg($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'audio/x-pn-realaudio-plugin') {
        // RealMedia file
        $code = resourcelib_embed_real($fullurl, $title, $clicktoopen);

    } else {
        // anything else - just try object tag enlarged as much as possible
        $code = resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype);
    }

    webctimport_print_header($url, $cm, $course);
    webctimport_print_heading($url, $cm, $course);

    echo $code;

    webctimport_print_intro($url, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

/**
 * Decide the best diaply format.
 * @param object $url
 * @return int display type constant
 */
function webctimport_get_final_display_type($url) {
    global $CFG;

    if ($url->display != RESOURCELIB_DISPLAY_AUTO) {
        return $url->display;
    }

    // detect links to local moodle pages
    if (strpos($url->externalurl, $CFG->wwwroot) === 0) {
        if (strpos($url->externalurl, 'file.php') === false and strpos($url->externalurl, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg', 'video/mp4',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = resourcelib_guess_url_mimetype($url->externalurl);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}

