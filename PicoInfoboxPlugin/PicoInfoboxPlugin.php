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

    protected $plugins_url = ''; 

    /**
     * Triggered before Pico renders the page
     * @see    Pico::getTwig()
     * @param  Twig_Environment &$twig          twig template engine
     * @param  array            &$twigVariables template variables
     * @param  string           &$templateName  file name of the template
     * @return void
     */
    public function onPageRendering(Twig_Environment &$twig, array &$twigVariables, &$templateName) {
        // Set plugins url
        $this->plugins_url = $twigVariables['plugins_url'];
    }

    /**
	 * Triggered after Pico has prepared the raw file contents for parsing
	 *
	 * @see	Pico::parseFileContent()
	 * @see	DummyPlugin::onContentParsed()
	 * @param  string &$content prepared file contents for parsing
	 * @return void
	 */
	public function onContentPrepared(&$content) {
        // Search for shortcodes allover the content
        preg_match_all('#\[(info|tip|note|warning) *(.*?)\](.*?)\[/(info|tip|note|warning)\]#s', $content, $matches);

        if (count($matches) > 0) {
            // Add the style tag to format the boxes
            //$new_content = $this->generateStyle() . $new_content;
        
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
                $content = preg_replace($target, $this->generateContent($options), $content, 1);
            }
        }
	}

    /**
	 * Triggered after Pico has rendered the page
	 *
	 * @param  string &$output contents which will be sent to the user
	 * @return void
	 */
	public function onPageRendered(&$output) {
		// Add CSS to end of <head>
		$output = str_replace('</head>', '<link rel="stylesheet" href="'.$this->plugins_url.'/PicoInfoboxPlugin/style.css" type="text/css"/></head>', $output);
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