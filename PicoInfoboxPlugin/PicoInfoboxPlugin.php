<?php

/**
 * ShareToolsPlugin - Embed youtube videos in wordpress-like shortcode format
 *
 * 
 * @author  Saad Bouteraa <s4ad@github>
 * @link    http://fb.com/sa4db
 * @license http://opensource.org/licenses/MIT
 * @version 0.1
 */
final class PicoInfoboxPlugin extends AbstractPicoPlugin
{
    /**
     * This plugin is enabled by default?
     *
     * @see AbstractPicoPlugin::$enabled
     * @var boolean
     */
    protected $enabled = true;

    /**
     * This plugin depends on ...
     *
     * @see AbstractPicoPlugin::$dependsOn
     * @var string[]
     */
    protected $dependsOn = array();

    /**
     * Triggered before Pico renders the page
     * @see    Pico::getTwig()
     * @param  Twig_Environment &$twig          twig template engine
     * @param  array            &$twigVariables template variables
     * @param  string           &$templateName  file name of the template
     * @return void
     */
    public function onPageRendering(Twig_Environment &$twig, array &$twigVariables, &$templateName)
    {
        // Search for shortcodes allover the content
        preg_match_all('#\[(info|tip|note|warning) *(.*?)\](.*?)\[/(info|tip|note|warning)\]#s', $twigVariables['content'], $matches);

        // Get page content
        $new_content = &$twigVariables['content'];

        if (count($matches) > 0) {
            // Add the style tag to format the boxes
            $new_content = $this->generateStyle() . $new_content;
        
            // Walk through matches one by one
            for ($i = 0; $i < count($matches[0]); $i++) {
                // Ignore if opening and closing tags are not the same
                if ($matches[1][$i] != $matches[4][$i]) {
                    continue;
                }

                // Store the options
                $options = array();
                
                // Parse tag options
                if (strlen($matches[2][$i]) > 0) {
                    foreach (explode("|", $matches[2][$i]) as $option) {
                        $split = explode("=", $option);
                        $options[$split[0]] = $split[1];
                    }
                }

                // Convert to boolean or set true if no value is specified
                if (array_key_exists("icon", $options)) {
                    $options["icon"] = $options["icon"] === "true";
                } else {
                    $options["icon"] = true;
                }

                $options["content"] = $matches[3][$i];
                $options["type"] = $matches[1][$i];
                
                // Escape the regex match to replace it properly
                $target = "#" . preg_quote($matches[0][$i]) . "#s";

                // Replace the match with html in the content
                $new_content = preg_replace($target, $this->generateContent($options), $new_content, 1);
            }
        }
    }

    private function generateStyle() {
        return "<style>
        :root {
            --text-color: #000;

            --info-color: #fcfcfc;
            --info-border: 1px solid #ccc;
            --info-icon: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' %3E%3Cpath d='M0 0h24v24H0V0z' fill='none'/%3E%3Cpath fill='%234a6785' d='M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z'/%3E%3C/svg%3E\");
            
            --tip-color: #f3f9f4;
            --tip-border: 1px solid #38b050;
            --tip-icon: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2314892C'%3E%3Cpath d='M0 0h24v24H0V0z' fill='none'/%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29L5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z'/%3E%3C/svg%3E\");
            
            --note-color: #fffdf6;
            --note-border: 1px solid #d59b00;
            --note-icon: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23AC6F00'%3E%3Cpath d='M4.47 21h15.06c1.54 0 2.5-1.67 1.73-3L13.73 4.99c-.77-1.33-2.69-1.33-3.46 0L2.74 18c-.77 1.33.19 3 1.73 3zM12 14c-.55 0-1-.45-1-1v-2c0-.55.45-1 1-1s1 .45 1 1v2c0 .55-.45 1-1 1zm1 4h-2v-2h2v2z'/%3E%3C/svg%3E\");
            
            --warning-color: #FFF8F7;
            --warning-border: 1px solid #CF4336;
            --warning-icon: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' enable-background='new 0 0 24 24' viewBox='0 0 24 24' fill='%23CF4336'%3E%3Cg%3E%3Cpath d='M0,0h24v24H0V0z' fill='none'/%3E%3C/g%3E%3Cg%3E%3Cpath d='M14.9,3H9.1C8.57,3,8.06,3.21,7.68,3.59l-4.1,4.1C3.21,8.06,3,8.57,3,9.1v5.8c0,0.53,0.21,1.04,0.59,1.41l4.1,4.1 C8.06,20.79,8.57,21,9.1,21h5.8c0.53,0,1.04-0.21,1.41-0.59l4.1-4.1C20.79,15.94,21,15.43,21,14.9V9.1c0-0.53-0.21-1.04-0.59-1.41 l-4.1-4.1C15.94,3.21,15.43,3,14.9,3z M15.54,15.54L15.54,15.54c-0.39,0.39-1.02,0.39-1.41,0L12,13.41l-2.12,2.12 c-0.39,0.39-1.02,0.39-1.41,0l0,0c-0.39-0.39-0.39-1.02,0-1.41L10.59,12L8.46,9.88c-0.39-0.39-0.39-1.02,0-1.41l0,0 c0.39-0.39,1.02-0.39,1.41,0L12,10.59l2.12-2.12c0.39-0.39,1.02-0.39,1.41,0l0,0c0.39,0.39,0.39,1.02,0,1.41L13.41,12l2.12,2.12 C15.93,14.51,15.93,15.15,15.54,15.54z'/%3E%3C/g%3E%3C/svg%3E\");
        }

        [data-theme=\"dark\"] {
            --text-color: #fff;

            --info-color: #434343;
            --info-border: 1px solid #ccc;
            --info-icon: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' %3E%3Cpath d='M0 0h24v24H0V0z' fill='none'/%3E%3Cpath fill='%23ccc' d='M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z'/%3E%3C/svg%3E\");
            
            --tip-color: #204527;
            --tip-border: 1px solid #38b050;
            --tip-icon: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2338b050'%3E%3Cpath d='M0 0h24v24H0V0z' fill='none'/%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29L5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z'/%3E%3C/svg%3E\");
            
            --note-color: #473d18;
            --note-border: 1px solid #d59b00;
            --note-icon: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23d59b00'%3E%3Cpath d='M4.47 21h15.06c1.54 0 2.5-1.67 1.73-3L13.73 4.99c-.77-1.33-2.69-1.33-3.46 0L2.74 18c-.77 1.33.19 3 1.73 3zM12 14c-.55 0-1-.45-1-1v-2c0-.55.45-1 1-1s1 .45 1 1v2c0 .55-.45 1-1 1zm1 4h-2v-2h2v2z'/%3E%3C/svg%3E\");
            
            --warning-color: #431f19;
            --warning-border: 1px solid #CF4336;
            --warning-icon: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' enable-background='new 0 0 24 24' viewBox='0 0 24 24' fill='%23CF4336'%3E%3Cg%3E%3Cpath d='M0,0h24v24H0V0z' fill='none'/%3E%3C/g%3E%3Cg%3E%3Cpath d='M14.9,3H9.1C8.57,3,8.06,3.21,7.68,3.59l-4.1,4.1C3.21,8.06,3,8.57,3,9.1v5.8c0,0.53,0.21,1.04,0.59,1.41l4.1,4.1 C8.06,20.79,8.57,21,9.1,21h5.8c0.53,0,1.04-0.21,1.41-0.59l4.1-4.1C20.79,15.94,21,15.43,21,14.9V9.1c0-0.53-0.21-1.04-0.59-1.41 l-4.1-4.1C15.94,3.21,15.43,3,14.9,3z M15.54,15.54L15.54,15.54c-0.39,0.39-1.02,0.39-1.41,0L12,13.41l-2.12,2.12 c-0.39,0.39-1.02,0.39-1.41,0l0,0c-0.39-0.39-0.39-1.02,0-1.41L10.59,12L8.46,9.88c-0.39-0.39-0.39-1.02,0-1.41l0,0 c0.39-0.39,1.02-0.39,1.41,0L12,10.59l2.12-2.12c0.39-0.39,1.02-0.39,1.41,0l0,0c0.39,0.39,0.39,1.02,0,1.41L13.41,12l2.12,2.12 C15.93,14.51,15.93,15.15,15.54,15.54z'/%3E%3C/g%3E%3C/svg%3E\");
        }

        .pico-infobox {
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
            border-radius: 5px;
            color: #333;
            margin: 10px 0 1em 0;
            padding: 10px 10px 10px 36px;
            position: relative;
        }

        .pico-infobox-icon {
            width: 16px;
            height: 16px;
            display: block;
            left: 10px;
            line-height: 20px;
            position: absolute;
            top: 12px;
            vertical-align: text-bottom;
            background-repeat: no-repeat;
            background-position: center;
        }

        .pico-infobox-info {
            background: var(--info-color);
            border: var(--info-border);
        }

        .pico-infobox-info-icon {
            background-image: var(--info-icon);
        }

        .pico-infobox-tip {
            background: var(--tip-color);
            border: var(--tip-border);
        }

        .pico-infobox-tip-icon {
            background-image: var(--tip-icon);
        }

        .pico-infobox-note {
            background: var(--note-color);
            border: var(--note-border);
        }

        .pico-infobox-note-icon {
            background-image: var(--note-icon);
        }

        .pico-infobox-warning {
            background: var(--warning-color);
            border: var(--warning-border);
        }

        .pico-infobox-warning-icon {
            background-image: var(--warning-icon);
        }

        .pico-infobox-title {
            font-weight: bold;
        }

        .pico-infobox-body {
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            line-height: 20px;
            min-height: 20px;
            color: var(--text-color);
        }</style>";
    }

    private function generateContent(array &$options) {
        $content = '<div class="pico-infobox pico-infobox-' . $options["type"] . '">';

        // Add the icon if desired
        if ($options["icon"]) {
            $content .= '<div class="pico-infobox-icon pico-infobox-' . $options["type"] . '-icon"></div>';
        }
        // Add the body
        $content .= '<div class="pico-infobox-body">';
            
        // Add the title if it exists
        if (array_key_exists("title", $options)) {
            $content .= '<div class="pico-infobox-title">' . $options["title"] . '</div>';
        }
        // Add the content
        $content .= '<div class="pico-infobox-content">' . $options["content"] .'</div></div></div>';
        
        // Trim leading and trailing whitespaces
        return trim($content);
    }
}