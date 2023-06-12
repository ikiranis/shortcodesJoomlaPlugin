<?php defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

use \Joomla\CMS\Plugin\CMSPlugin;

Class plgContentShortcodes extends CMSPlugin {


    function __construct( &$subject, $params ) {
        parent::__construct( $subject, $params );
    }

    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {

        $html = '';

        if (is_object($article))
        {
            $article->text = $this->doReplace($article->text, $html);
            return $article;
        }

        return true;
    }

    protected function doReplace($text, $replace)
    {
        $text = $this->replaceIcons($text);
        $text = $this->replaceBubble($text);
        $text = $this->replaceSlider($text);
        $text = $this->replaceAccordion($text);

        return $text;
    }

    private function replaceSlider($text) {
        $pattern = '/{slider=(.*?)}(.*?){\/slider}/s';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        if(empty($matches)) {
            return $text;
        }

        JHtml::_('stylesheet', 'plg_shortcodes/slider.css', array('version' => 'auto', 'relative' => true));

        // Loop through the matches and replace them with details
        foreach ($matches as $match) {
            $sliderTitle = $match[1];
            $sliderContent = $match[2];

            $replacement = '
                <details class="slider">
                    <summary>' . $sliderTitle . '</summary>'
                    . $sliderContent .
                '</details>';

            $text = preg_replace($pattern, $replacement, $text, 1);
        }

        $text = $this->removeEmptyParagraphs($text);

        return $text;

    }

    private function replaceAccordion($text) {
        // Get text between [accordion] and [/accordion]
        $pattern = "/\[accordion\](.*?)\[\/accordion\]/s";
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $text = preg_replace($pattern, $match[1], $text, 1);
        }

//        print_r($text);

        $matches = null;

        $pattern = "/\[accordion_item title='(.*?)'\](.*?)\[\/accordion_item\]/s";
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        if(empty($matches)) {
            return $text;
        }

        JHtml::_('stylesheet', 'plg_shortcodes/accordion.css', array('version' => 'auto', 'relative' => true));

        // Loop through the matches and replace them with details
        foreach ($matches as $match) {
            $accordionTitle = $match[1];
            $accordionContent = $match[2];

            $replacement = '
                <details>
                    <summary>' . $accordionTitle . '</summary>'
                . '<div class="detailsContent">' . $accordionContent . '</div>' .
                '</details>';

            $text = preg_replace($pattern, $replacement, $text, 1);
        }

        $text .= '
            <script>
                // Retrieve all the summary elements
                const summaries = document.querySelectorAll("summary");
                
                // Add click event listener to each summary element
                summaries.forEach(function (summary) {
                  summary.addEventListener("click", function () {
                    const details = this.parentNode;
                    const detailsContent = this.nextElementSibling;
                
                    // Close all other details elements
                    summaries.forEach(function (otherSummary) {
                      const otherDetails = otherSummary.parentNode;
                      const otherDetailsContent = otherSummary.nextElementSibling;
                
                      if (otherSummary !== summary && otherDetails.hasAttribute("open")) {
                        otherDetails.removeAttribute("open");
                        otherDetailsContent.style.maxHeight = 0;
                        otherDetailsContent.scrollIntoView({ behavior: "smooth" });
                      }
                    });
                
                    // Toggle the current details element
                    if (details.hasAttribute("open")) {
                      details.removeAttribute("open");
                      detailsContent.style.maxHeight = null;
                    } else {
                      detailsContent.style.maxHeight = detailsContent.scrollHeight + "px";
                      detailsContent.scrollIntoView({ behavior: "smooth" });
                    }
                  });
                });
            </script>
        ';

        return $text;
    }

    /**
     * Replace bubble shortcode with html
     *
     * @param $text
     * @return array|string|string[]|null
     */
    private function replaceBubble($text) {
        $pattern = '/\[bubble\s+background="([^"]+)"\s+color="([^"]+)"\s+author="([^"]+)"\]\s*([^[]+)\s*\[\/bubble\]/';

        return preg_replace_callback($pattern, function($matches) {
            $background = $matches[1];
            $color = $matches[2];
            $author = $matches[3];
            $content = $matches[4];

            $html = '<div class="bubble" style="background-color: ' . $background . '; color: ' . $color . '; padding: 1em;">';
            $html .= '<div class="bubble-content">' . $content . '</div>';
            $html .= '<cite class="bubble-author"><span style="border:15px solid ' . $background . '"></span>' . $author . '</cite>';
            $html .= '</div>';

            return $html;
        }, $text);
    }

    /**
     * Replace icons shortcode with svg
     *
     * @param $text
     * @return array|mixed|string|string[]|null
     */
    private function replaceIcons($text) {
        $icons = [
            'icon-phone' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
                </svg>',
            'icon-envelope' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                  <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757Zm3.436-.586L16 11.801V4.697l-5.803 3.546Z"/>
                </svg>'
        ];

        // Get all the matches with [icon] shortcode
        $pattern = '/\[icon name="(.*)"\]/';
        preg_match_all($pattern, $text, $matches);

        // Loop through the matches and replace them with the icon
        foreach ($matches[1] as $match) {
            // replace the pattern with the icon
            $replacement = $icons[$match];

            $text = preg_replace($pattern, $replacement, $text, 1);
        }

        return $text;
    }

    private function removeEmptyParagraphs($text) {
        $pattern = '/<p>(.?)<\/p>/s';
        $replacement = '';

        return preg_replace($pattern, $replacement, $text);
    }
}
