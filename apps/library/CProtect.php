<?php

//Grigoriy Chorniy
class CProtect {

    public static function arrayInt($array) {
        foreach ($array as $k => $v)
            $array[$k] = (int) $v;
        return $array;
    }

    public static function int($val) {
        return (int) $val;
    }

    public static function float($val) {
        return (float) $val;
    }

    public static function real($val) {
        return (real) $val;
    }

    public static function double($val) {
        return (double) $val;
    }

    public static function bool($val) {
        return (bool) $val;
    }

    /*public static function _array($val) {
        return ($val == NULL) ? array() : (array) $val;
    }

    public static function _object($val) {
        return (object) $val;
    }*/

    public static function string($val, $maxlength = 255) {
        $CClearText = CClearText::getInstance();
        $CClearText->setMaxLength($maxlength);
        $CClearText->setCutText(true, true);
        $CClearText->setAllowedTags('');
        $CClearText->setAllowedProperties('');
        $CClearText->setAllowedAttributes('');
        return $CClearText->prepare(trim($val));
    }

    public static function short_html($val, $maxlength = 512) {
        $CClearText = CClearText::getInstance();
        $CClearText->setMaxLength($maxlength);
        $CClearText->setCutText(true, true);
        $CClearText->setAllowedTags('<p><b><a><i><em><li><ul><ol><u><strong><br><div><span>');
        $CClearText->setAllowedProperties('');
        $CClearText->setAllowedAttributes('');
        return $CClearText->prepare(trim($val));
    }

    public static function tiny_html($val, $maxlength = 512) {
        $CClearText = CClearText::getInstance();
        $CClearText->setMaxLength($maxlength);
        $CClearText->setCutText(true, true);
        $CClearText->setAllowedTags('<p><b><a><i><em><li><ul><ol><u><strong><br><div><span><h2><h3><h4><h5><img>');
        $CClearText->setAllowedProperties('text-align');
        $CClearText->setAllowedAttributes('style');
        return $CClearText->prepare(trim($val));
    }

    public static function html($val, $maxlength = 65000) {
        $CClearText = CClearText::getInstance();
        $CClearText->setMaxLength($maxlength);
        $CClearText->setCutText(true, true);
        $CClearText->setAllowedTags('<p><b><a><i><em><li><ul><ol><u><strong><br><div><span><h2><h3><h4><h5>');
        $CClearText->setAllowedProperties('');
        $CClearText->setAllowedAttributes('');
        return $CClearText->prepare(trim($val));
    }

}

//Grigoriy Chorniy
class CClearText {

    private $text = '';
    private $search = array();
    private $replacement = array();
    private $allowed_tags = '';
    private $allowed_attributes = '';
    private $allowed_properties = '';
    private $max_length = 0;
    private $cut_text = false;
    private $cut_text_without_ellipsis = false;
    protected static $_instance = null;

    public function __construct($text = '', $charset = 'UTF-8') {
        if (!is_null(self::$_instance) && (self::$_instance instanceof CClearText))
            return self::$_instance;
        mb_internal_encoding($charset);
        $this->setText($text);
    }

    public static function &getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function setText($text = '') {
        //$this->text=nl2br(stripcslashes(trim($text)));
        $this->text = rawurldecode(stripcslashes(trim($text)));
    }

    public function setCutText($cut_text = false, $without_ellipsis = true) {
        $this->cut_text = $cut_text;
        $this->cut_text_without_ellipsis = $without_ellipsis;
    }

    public function setAllowedTags($allowed_tags = '') {
        $this->allowed_tags = $allowed_tags;
    }

    public function setMaxLength($max_length = 0) {
        $this->max_length = (int) $max_length;
    }

    public function setAllowedAttributes($allowed_attributes = '') {
        $this->allowed_attributes = str_replace(',', '|', $allowed_attributes);
    }

    public function setAllowedProperties($allowed_properties = '') {
        $this->allowed_properties = $allowed_properties;
    }

    private function code2utf($num) {
        if ($num < 128)
            return chr($num);
        if ($num < 2048)
            return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
        if ($num < 65536)
            return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        if ($num < 2097152)
            return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        return '';
    }

    private function convertHtmlCodes() {
        $this->search[] = '~&#0*(\d+);~e';
        $this->replacement[] = '$this->code2utf($1)';
        $this->text = htmlspecialchars_decode($this->text);

        $this->text = str_replace(array('&amp;amp;', '&amp;lt;', '&amp;gt;'), array('&amp;amp;amp;', '&amp;amp;lt;', '&amp;amp;gt;'), $this->text);
        $this->text = preg_replace('/(&amp;#*\w+)[\x00-\x20]+;/u', '$1;', $this->text);
        $this->text = preg_replace('/(&amp;#x*[0-9A-F]+);*/iu', '$1;', $this->text);
        $this->text = html_entity_decode($this->text, ENT_COMPAT, 'UTF-8');
    }

    private function removeNotHtmlObjects() {
        $this->search[] = '@<script[^>]*?>.*?</script>@si';
        $this->replacement[] = '';
        $this->search[] = '@<style[^>]*?>.*?</style>@siU';
        $this->replacement[] = '';
        $this->search[] = '@<![\s\S]*?--[ \t\n\r]*>@';
        $this->replacement[] = '';
        $this->search[] = '@<object[^>]*?>.*?</object>@si';
        $this->replacement[] = '';
    }

    private function convertExternalLinks() {
        $this->text = str_replace(array("\\r", "\\n", "\\n\\r"), array("\r", "\n<BR>", "\n\r"), $this->text);
        $in = array(
            '/<a([^>]+)href="([a-z]+):\/\/(?!' . str_replace('.', '\.', $_SERVER['HTTP_HOST']) . ')([^>]*)"([^>]*)>([^>]*)<\/a>/is'
        );
        $out = array(
            '<noindex><a $1 href="$2://$3" $4 rel="nofollow" target="_blank">$5</a></noindex> ',
        );
        return preg_replace($in, $out, $this->text);
    }

    private function cutText($text = '', $length = 255, $without_ellipsis = false) {
        $original_text = trim($text);
        $text_length = mb_strlen($original_text);
        if ($text_length > $length) {
            $whitespaceposition = mb_strpos($text, " ", $length);
            if ($whitespaceposition > 0 && $whitespaceposition <= $length)
                $text = mb_substr($text, 0, ($whitespaceposition));
            else {
                $whitespaceposition = mb_strrpos(mb_substr($text, 0, $length), ' ');
                $text = mb_substr($text, 0, $whitespaceposition);
            }
        }
        if (mb_strlen(trim($text)) == 0 && $text_length > 0) {
            $text = mb_substr($original_text, 0, $length);
        }
        return $text . (($without_ellipsis) ? '' : "...");
    }

    private function getTagName($text, $cnt) {
        while (($cnt < mb_strlen($text)) && (eregi("[^a-z0-9]", mb_substr($text, $cnt, 1))))
            $cnt++;
        $tagNameStart = $cnt;
        while (($cnt < mb_strlen($text)) && (!eregi("[^a-z0-9]", mb_substr($text, $cnt, 1))))
            $cnt++;
        return mb_substr($text, $tagNameStart, $cnt - $tagNameStart);
    }

    private function getTagEnd($text, $cnt) {
        return mb_strpos($text, ">", $cnt);
    }

    private function closeUnclosedTags($unclosedString) {
        // created by Adam Gundry, http://www.agbs.co.uk
        preg_match_all("/<([^\/]\w*)>/", $closedString = $unclosedString, $tags);
        for ($i = count($tags[1]) - 1; $i >= 0; $i--) {
            $tag = $tags[1][$i];
            if (!(in_array($tag, array('br', 'hr'))) && substr_count($closedString, "</$tag>") < substr_count($closedString, "<$tag>"))
                $closedString .= "</$tag>";
        }
        return $closedString;
    }

    private function closeUnclosedTags2($text) {
        $openedTags = array();
        $cnt = 0;
        while (($cnt < mb_strlen($text)) && (true)) {
            if (mb_substr($text, $cnt, 2) == "</") {
                $tagEnd = $this->getTagEnd($text, $cnt);
                $tagName = $this->getTagName($text, $cnt);
                $lastOpenedTag = array_pop($openedTags);
                if ($lastOpenedTag == NULL) {
                    //error: no tag was opened - delete the close tag
                    $text = mb_substr($text, 0, $cnt) . mb_substr($text, $tagEnd + 1);
                } elseif ($lastOpenedTag != $tagName) {
                    //error: closing unopened tag (possible cross-nesting) - return the original opened tag and delete the close tag
                    array_push($openedTags, $lastOpenedTag);
                    $text = mb_substr($text, 0, $cnt) . mb_substr($text, $tagEnd + 1);
                } else {
                    $cnt = $tagEnd + 1;
                }
            } elseif (mb_substr($text, $cnt, 1) == "<") {
                $tagEnd = $this->getTagEnd($text, $cnt);
                $tagName = $this->getTagName($text, $cnt);
                if (mb_substr($text, $tagEnd - 1, 1) != "/")
                    array_push($openedTags, $tagName);
                $cnt = $tagEnd + 1;
            }
            else {
                $cnt++;
            }
        }
        while (count($openedTags) > 0) {
            $tag = array_pop($openedTags);
            //if(in_array($tag,array('br','hr')))continue;
            $text.="</" . $tag . ">";
        }
        return $text;
    }

    private function remove_not_allowed_properties($text) {
        $text = preg_replace('/<(.*?)>/ie', "'<' . preg_replace('/([^;\"]+)?(?<!$this->allowed_properties):(?!\/\/(.+?)\/)((.*?)[^;\"]+)(;)?/is', '', stripcslashes('$1')) . '>'", $text);
        //$text=preg_replace('/([^;"]+)?(?<!'. $this->allowed_properties .'):(?!\/\/(.+?)\/)((.*?)[^;"]+)(;)?/isU', '', $text);
        return $text;
    }

    public function prepare($text = '') {
        $this->setText($text);

        $this->search = array(
            '/%0[0-8bcef]/', // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/', // url encoded 16-31
            '/[\x00-\x08]/', // 00-08
            '/\x0b/', '/\x0c/', // 11, 12
            '/[\x0e-\x1f]/'    // 14-31
        );
        $this->replacement = array(
            '', // url encoded 00-08, 11, 12, 14, 15
            '', // url encoded 16-31
            '', // 00-08
            '', '', // 11, 12
            ''    // 14-31
        );


        /*
         * Validate standard character entities
         *
         * Add a semicolon if missing.  We do this to enable
         * the conversion of entities to ASCII later.
         *
         */
        $this->search[] = '#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i';
        $this->replacement[] = "\\1;\\2";


        /*
         * Validate UTF16 two byte encoding (x00)
         *
         * Just as above, adds a semicolon if missing.
         *
         */
        $this->search[] = '#(&\#x?)([0-9A-F]+);?#i';
        $this->replacement[] = "\\1\\2;";

        //dump($this->text);


        $this->convertHtmlCodes();
        $this->removeNotHtmlObjects();
        $this->text = preg_replace($this->search, $this->replacement, $this->text);
        //$this->text 			= 	$this->closeUnclosedTags($this->text);
        $this->text = strip_tags($this->text, $this->allowed_tags);
        $this->text = preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/\s+/',\"/'/\",'/\s*\=\s*/i','/([a-z_-]+)=[^\'\"].*\s/iU','/\s[a-z_-]\s/iU','/(?<!$this->allowed_attributes)(\=\".*\")/iU','/(\s[a-z_-]+=\"\")/iU'), array(' ','\"','=','','','=\"\"',''), stripcslashes('$1')) . '>'", $this->text);
        $this->text = $this->remove_not_allowed_properties($this->text);
        $this->text = strip_tags($this->text, $this->allowed_tags);
        $this->text = $this->convertExternalLinks();
        $this->text = strip_tags($this->text, $this->allowed_tags);

        if ($this->max_length > 0) {
            if ($this->cut_text)
                $this->text = $this->cutText($this->text, $this->max_length, $this->cut_text_without_ellipsis);
            else
                $this->text = mb_substr($this->text, 0, $this->max_length);
        }
        return $this->text;
    }

    public function getText() {
        return $this->text;
    }

}

?>