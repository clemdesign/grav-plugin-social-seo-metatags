<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;


/**
 * Class SocialSEOMetaTagsPlugin
 * @package Grav\Plugin
 */
class SocialSEOMetaTagsPlugin extends Plugin
{

  /** ---------------------------
   * Private/protected properties
   * ----------------------------
   */

  /**
   * Instance of SocialSEOMetaTagsPlugin class
   *
   * @var object
   */
  private     $desc;
  private     $title;

  /**
   * @return array
   *
   * The getSubscribedEvents() gives the core a list of events
   *     that the plugin wants to listen to. The key of each
   *     array section is the event that the plugin listens to
   *     and the value (in the form of an array) contains the
   *     callable (or function) as well as the priority. The
   *     higher the number the higher the priority.
   */
  public static function getSubscribedEvents()
  {

    return [
        'onPluginsInitialized' => ['onPluginsInitialized', 0]
    ];
  }


  /**
   * Initialize the plugin
   */
  public function onPluginsInitialized()
  {

    if (    !$this->isAdmin()
        and $this->config->get('plugins.social-seo-metatags.enabled')
        ) {
          $this->enable([
              'onPageInitialized'     => ['onPageInitialized', 0]
          ]);
        }
  }

  public function onPageInitialized(Event $e)
  {
    $page = $this->grav['page'];
    //Get values
    $meta = $page->metadata(null);
    $this->desc = $this->sanitizeMarkdowns(strip_tags($page->summary()));
    $this->title = $this->sanitizeMarkdowns($this->grav['page']->title());

    //Pre-treament
    if(strlen($this->desc)>160)
    {
      // Remove last (truncated) word and replace by ...
      $desc_temp = substr($this->desc,0,157);
      $this->desc = substr($desc_temp, 0, strrpos($desc_temp, ' '))."...";
    }
    if($this->desc == "")
    {
      $this->desc = $this->config->get('site.metadata.description');
    }

    //Apply change
    $meta = $this->getSEOMetatags($meta);
    $meta = $this->getTwitterCardMetatags($meta);
    $meta = $this->getFacebookMetatags($meta);
    $page->metadata($meta);
  }

  private function getSEOMetatags($meta){
    if($this->config->get('plugins.social-seo-metatags.enabled'))
    {
      $page         = $this->grav["page"];
      $page_header  = $page->header();
      $keywords     = "";
      /**
       *  SEO Description
       **/
      $meta['description']['name']      = 'description';
      $meta['description']['content']   = $this->desc;

      /**
       *  SEO Keywords
       **/
      if(isset($page_header->keywords))
      {
        $keywords = $page_header->keywords;
      }
      else
      {
        $length = $this->config->get('plugins.social-seo-metatags.seo.keywords.length');
        if($length < 1) $length = 20;
        // From Taxomany
        if($this->config->get('plugins.social-seo-metatags.seo.keywords.taxonomy.enabled'))
        {
          if (array_key_exists( 'category', $page->taxonomy() )) { $categories = $page->taxonomy()['category']; } else { $categories = []; }
          if (array_key_exists( 'tag', $page->taxonomy() )) { $tags = $page->taxonomy()['tag']; } else { $tags = []; }
          $taxonomy = array_merge ($categories, $tags);
          $taxonomy = array_unique ($taxonomy);
        }
        else
        {
          $taxonomy = [];
        }

        // From Page Content
        if($this->config->get('plugins.social-seo-metatags.seo.keywords.page_content.enabled'))
        {
          $content = $page->getRawContent();
          $content = str_replace("\n", " ", $content);
          $matches = [];
          if(preg_match_all('|<strong>(.*)</strong>|U', $content, $matches) > 0) { $strong_words = $matches[1]; } else { $strong_words = []; }
          if(preg_match_all('|<em>(.*)</em>|U', $content, $matches) > 0) { $em_words = $matches[1]; } else { $em_words = []; }
          $content_words = array_merge ($strong_words, $em_words);
          $content_words = $this->cleanKeywords($content_words);
          $content_words = array_unique ($content_words);
        }
        else
        {
          $content_words= [];
        }

        if((count($taxonomy)>0) || (count($content_words)>0))
        {
          $keywords_tab = array_merge ($taxonomy, $content_words);
          $keywords_tab = array_unique ($keywords_tab);
          $keywords_tab = array_slice($keywords_tab, 0, $length);
          $keywords = join(',',$keywords_tab);
        }
      }

      if($keywords != "")
      {
        $meta['keywords']['name']      = 'keywords';
        $meta['keywords']['content']   = strip_tags($keywords);
      }

      /**
       *  SEO Robots
       **/
      if(isset($page_header->robots))
      {
        $meta['robots']['name']       = 'robots';
        $meta['robots']['content']    = $page_header->robots;
      }
      else
      {
        switch ($this->config->get('plugins.social-seo-metatags.seo.robots'))
        {
          case "index_follow":
            $meta['robots']['name']       = 'robots';
            $meta['robots']['content']    = 'index, follow';
            break;
          case "noindex_follow":
            $meta['robots']['name']       = 'robots';
            $meta['robots']['content']    = 'noindex, follow';
            break;
          case "index_nofollow":
            $meta['robots']['name']       = 'robots';
            $meta['robots']['content']    = 'index, nofollow';
            break;
          case "noindex_nofollow":
            $meta['robots']['name']       = 'robots';
            $meta['robots']['content']    = 'noindex, nofollow';
            break;
          case "noarchive":
            $meta['robots']['name']       = 'robots';
            $meta['robots']['content']    = 'noarchive';
            break;
          case "noodp":
            $meta['robots']['name']       = 'robots';
            $meta['robots']['content']    = 'noodp';
            break;
          case "nosnippet":
            $meta['robots']['name']       = 'robots';
            $meta['robots']['content']    = 'nosnippet';
            break;
          default:
            // Without metatag
            break;
        }
      }
    }
    return $meta;
  }

  private function getTwitterCardMetatags($meta){

    if($this->grav['config']->get('plugins.social-seo-metatags.social_pages.pages.twitter.enabled')) {

      if (!isset($meta['twitter:card'])) {
        $meta['twitter:card']['name']      = 'twitter:card';
        $meta['twitter:card']['property']  = 'twitter:card';
        $meta['twitter:card']['content']   = $this->grav['config']->get('plugins.social-seo-metatags.social_pages.pages.twitter.type');
      }

      if (!isset($meta['twitter:title'])) {
        $meta['twitter:title']['name']     = 'twitter:title';
        $meta['twitter:title']['property'] = 'twitter:title';
        $meta['twitter:title']['content']  = $this->title;
      }

      if (!isset($meta['twitter:description'])) {
        $meta['twitter:description']['name']     = 'twitter:description';
        $meta['twitter:description']['property'] = 'twitter:description';
        $meta['twitter:description']['content']  = $this->desc;
      }

      if (!isset($meta['twitter:image'])) {
        if (!empty($this->grav['page']->value('media.image'))) {
          $images = $this->grav['page']->media()->images();
          $image  = array_shift($images);

          $meta['twitter:image']['name']     = 'twitter:image';
          $meta['twitter:image']['property'] = 'twitter:image';
          $meta['twitter:image']['content']  = $this->grav['uri']->base() . $image->url();
        }
      }

      if (!isset($meta['twitter:site'])) {
        //Get Twitter username
        $user = "@".$this->grav['config']->get('plugins.social-seo-metatags.social_pages.pages.twitter.username');
        //Update data
        $meta['twitter:site']['name']     = 'twitter:site';
        $meta['twitter:site']['property'] = 'twitter:site';
        $meta['twitter:site']['content']  = $user;
      }
    }
    return $meta;
  }

  private function getFacebookMetatags($meta){

    if($this->grav['config']->get('plugins.social-seo-metatags.social_pages.pages.facebook.enabled')){

      //Manually convert locale ll by ll_LL from page or default language
      $default_locale = $this->grav["page"]->language();
      if($default_locale == null) $default_locale = $this->grav['config']->get('site.default_lang');
      switch($default_locale)
      {
        case "fr":
          $locale = "fr_FR";
          break;
        case "en":
          $locale = "en_EN";
          break;
      }

      $meta['og:title']['property']       = 'og:title';
      $meta['og:title']['content']        = $this->title;

      $meta['og:description']['property'] = 'og:description';
      $meta['og:description']['content']  = $this->desc;

      $meta['og:type']['property']        = 'og:type';
      $meta['og:type']['content']         = 'article';

      if(isset($locale))
      {
        $meta['og:type']['property']        = 'og:locale';
        $meta['og:type']['content']         = $locale;
      }

      $meta['og:url']['property']         = 'og:url';
      $meta['og:url']['content']          = $this->grav['uri']->url(true);

      if (!empty($this->grav['page']->value('media.image'))) {
        $images = $this->grav['page']->media()->images();
        $image  = array_shift($images);

        $meta['og:image']['property']  = 'og:image';
        $meta['og:image']['content']   = $this->grav['uri']->base() . $image->url();
      }

      $meta['fb:app_id']['property']     = 'fb:app_id';
      $meta['fb:app_id']['content']      = $this->grav['config']->get('plugins.social-seo-metatags.social_pages.pages.facebook.appid');

    }
    return $meta;
  }

  private function sanitizeMarkdowns($text){
    $rules = array (
        '/(#+)(.*)/'                             => '\2',  // headers
        '/(&lt;|<)!--\n((.*|\n)*)\n--(&gt;|\>)/' => '',    // comments
        '/(\*|-|_){3}/'                          => '',    // hr
        '/!\[([^\[]+)\]\(([^\)]+)\)/'            => '',    // images
        '/\[([^\[]+)\]\(([^\)]+)\)/'             => '\1',  // links
        '/(\*\*|__)(.*?)\1/'                     => '\2',  // bold
        '/(\*|_)(.*?)\1/'                        => '\2',  // emphasis
        '/\~\~(.*?)\~\~/'                        => '\1',  // del
        '/\:\"(.*?)\"\:/'                        => '\1',  // quote
        '/```(.*)\n((.*|\n)+)\n```/'             => '\2',  // fence code
        '/`(.*?)`/'                              => '\1',  // inline code
        '/(\*|\+|-)(.*)/'                        => '\2',  // ul lists
        '/\n[0-9]+\.(.*)/'                       => '\2',  // ol lists
        '/(&gt;|\>)+(.*)/'                       => '\2',  // blockquotes
    );

    foreach ($rules as $regex => $replacement) {
      if (is_callable ( $replacement)) {
        $text = preg_replace_callback ($regex, $replacement, $text);
      } else {
        $text = preg_replace ($regex, $replacement, $text);
      }
    }

    $text = trim($text);

    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }

  private function cleanKeywords($array)
  {
    $new_array = [];
    foreach($array as $value)
    {
      $push_val = true;
      if(strlen($value) < 3)  $push_val = false;
      if(strlen($value) > 30) $push_val = false;
      if(preg_match("#([/|\\]{1})([0-9a-zA-Z_-]+)([.]{1})([0-9a-zA-Z_-]+)#",$value) > 0) $push_val = false;  //Remove path value
      if(substr_count($value, ' ') > 1)  $push_val = false;                                                  //Accept only 1 space in keyword value

      $value = preg_replace("/([^a-zA-Z0-9_éèêëàîïôöùûü' -]+)/", "", $value);                                //Remove all special char

      if($push_val) array_push($new_array, $value);
    }
    return $new_array;
  }

}
