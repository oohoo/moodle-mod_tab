<?php

namespace mod_tab\output;

require_once($CFG->dirroot . '/lib/resourcelib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');
require_once("$CFG->libdir/filelib.php");

defined('MOODLE_INTERNAL') || die();

class mobile
{
    /* Returns the initial page when viewing the activity for the mobile app.
     *
     * @param  array $args Arguments from tool_mobile_get_content WS
     * @return array HTML, javascript and other data
     */
    public static function mobile_view_activity($args)
    {
        global $OUTPUT, $DB, $CFG;

        $cm = get_coursemodule_from_id("tab", $args['cmid']);
        $tab = $DB->get_record("tab", array("id" => $cm->instance));
        $context = \context_module::instance($cm->id);
        $editoroptions = array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1, 'context' => $context, 'noclean' => 1, 'trusttext' => true);
        $options = $DB->get_records('tab_content', array('tabid' => $tab->id), 'tabcontentorder');

        $options = array_values($options);

        $data= [];
        $tabList = [];



        foreach (array_keys($options) as $key)
        {

            //New conditions now exist. Must verify if embedding a pdf or url
            //Content must change accordingly
            //$pdffile[$key] = $options[$key]->pdffile;


            $externalurl[$key] = $options[$key]->externalurl;
            //Eventually give option for height within the form. Pass this by others, because it could be confusing.
            $iframeheight[$key] = '800px';

            if (!empty($externalurl[$key]))
            {
                //todo check url
                if (!preg_match('{https?:\/\/}', $externalurl[$key]))
                {
                    $externalurl[$key] = 'http://' . $externalurl[$key];
                }


            }
            else
            {
                if (empty($options[$key]->format))
                {
                    $options[$key]->format = 1;
                }
                $content[$key] = \file_rewrite_pluginfile_urls($options[$key]->tabcontent, 'pluginfile.php', $context->id, 'mod_tab', 'content', $options[$key]->id);
                $content[$key] = \format_text($content[$key], $options[$key]->contentformat, $editoroptions, $context);
                //PDF
                $content2 = str_ireplace(array(' ', "\n", "\r", "\t", '&nbsp;'), array(), strip_tags($content[$key], '<a>'));

                if (stripos($content2, '<a') === 0 && stripos($content2, '</a>') >= strlen($content2) - 4)
                {
                    $start = strpos($content2, '"')+1;
                    $l = strpos($content2, '"', $start+1) - $start;

                    $href = substr($content2, $start, $l);
                    if(stripos($href, '.pdf') !== false)
                    {
                        $externalurl[$key] = $href;
                    }
                }
            }
            //Enter into proper div
            //Check for pdf
            $tabcontent = [];
            $tabcontent['title'] = $options[$key]->tabname ;
            if (!empty($externalurl[$key]) && preg_match('/\bpdf\b/i', $externalurl[$key]))
            {
                $tabcontent['url'] = mobile::process_urls($externalurl[$key]);
                array_push($tabList, $content);

                //$html_content = tab_embed_general(process_urls($externalurl[$key]), '', get_string('embed_fail_msg', 'tab') . "<a href='$externalurl[$key]' target='_blank' >" . get_string('embed_fail_link_text', 'tab') . '</a>', 'application/pdf');
            }
            elseif (!empty($externalurl[$key]))
            {
                $tabcontent['url']  = mobile::process_urls($externalurl[$key]);
                array_push($tabList, $content);
                //$html_content = tab_embed_general(process_urls($externalurl[$key]), '', get_string('embed_fail_msg', 'tab') . "<a href='$externalurl[$key]' target='_blank' >" . get_string('embed_fail_link_text', 'tab') . '</a>', 'text/html');
            }
            else
            {
                $tabcontent['html']  = $content[$key];
            }

            array_push($tabList, $tabcontent);
        }

        $data['tabList'] = $tabList;

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_tab/mobile_view', $data),
                ],
            ]
        ];

    }

    public static function process_urls($string) {
        preg_match_all("/<a href=.*?<\/a>/", $string, $matches);
        foreach ($matches[0] as $mtch) {
            $mtch_bits = explode('"', $mtch);
            $string = str_replace($mtch, "{$mtch_bits[1]}", $string);
        }
        $path = str_replace('<div class="text_to_html">', '', $string);
        $path = str_replace('</div>', '', $path);
        $path = str_replace('<p>', '', $path);
        $path = str_replace('</p>', '', $path);


        $string = $path;

        return $string;
    }
}


