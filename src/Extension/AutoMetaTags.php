<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Autometatags
 * @author      Mahmoud Magdy
 * @copyright   Copyright (C) 2025 All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\AutoMetaTags\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Event\Model\BeforeSaveEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Auto Meta Tags Content Plugin.
 * Automatically generates meta description and keywords for Joomla articles.
 *
 * @since 1.0.0
 */
class AutoMetaTags extends CMSPlugin implements SubscriberInterface
{
    /**
     * @var   array List of common words to ignore when generating keywords.
     * @since 1.0.0
     */
    private array $stopWords = [];

    /**
     * @var   int Minimum length for a word to be considered a keyword.
     * @since 1.0.0
     */
    private int $minKeywordLength = 3;

    /**
     * @var   int Maximum number of keywords to generate.
     * @since 1.0.0
     */
    private int $maxKeywordsCount = 7;

    /**
     * @var   int Target length for the meta description.
     * @since 1.0.0
     */
    private int $metaDescLength = 155;

    /**
     * Constructor.
     * Initializes plugin parameters and default stop words.
     *
     * @param   object  &$subject  The object to observe (Dispatcher).
     * @param   array<string, mixed>   $config    An optional array of configuration settings.
     *
     * @since 1.0.0
     */
    public function __construct($subject, array $config = [])
    {
        parent::__construct($subject, $config);

        // Load parameters from plugin settings
        $this->metaDescLength     = (int) $this->params->get('metadesc_length', 155);
        $this->maxKeywordsCount   = (int) $this->params->get('max_keywords_count', 7);
        $this->minKeywordLength   = (int) $this->params->get('min_keyword_length', 3);

        $stopWordsParam = $this->params->get('stop_words', '');
        if (is_string($stopWordsParam) && !empty($stopWordsParam)) {
            $this->stopWords = array_filter(array_map('trim', explode(',', $stopWordsParam)));
        }

        // Use default stop words if not provided or empty in parameters
        if (empty($this->stopWords)) {
            $this->stopWords = [
                'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'has', 'he',
                'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the', 'to', 'was', 'were',
                'will', 'with', 'i', 'you', 'me', 'my', 'we', 'our', 'they', 'them', 'their',
                'this', 'these', 'those', 'then', 'than', 'so', 'if', 'or', 'but', 'not',
            ];
        }
    }

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array<string, string> The events to subscribe to.
     *
     * @since   1.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentBeforeSave' => 'handleContentBeforeSave'
        ];
    }

    /**
     * Event handler for 'onContentBeforeSave'.
     * Triggered before an article is saved, allowing meta tags to be generated.
     *
     * @param   BeforeSaveEvent $event  The event instance containing article data.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function handleContentBeforeSave(BeforeSaveEvent $event): void
    {
        $context = $event->getContext();
        $article = $event->getItem();

        // Only process content articles
        if ($context !== 'com_content.article' && $context !== 'com_content.form') {
            return;
        }

        // Ensure we have a valid article object
        if (!is_object($article)) {
            return;
        }

        // Choose introtext or fulltext to form the source for meta generation
        $sourceText = '';
        if (isset($article->introtext) && !empty($article->introtext)) {
            $sourceText .= $article->introtext . ' ';
        }
        else if (isset($article->fulltext) && !empty($article->fulltext)) {
            $sourceText .= $article->fulltext;
        }
        $sourceText = trim($sourceText);

        if (empty($sourceText)) {
            return; // No text content to process
        }

        // Generate meta description if it's not already set
        if (isset($article->metadesc) && empty($article->metadesc)) {
            $this->generateMetaDescription($article, $sourceText);
        }

        // Generate meta keywords if they are not already set
        if (isset($article->metakey) && empty($article->metakey)) {
            $this->generateMetaKeywords($article, $sourceText);
        }
    }

    /**
     * Generates and sets the meta description for the article.
     *
     * @param   object  $articleObject  The article data object.
     * @param   string  $sourceText     The article's text content.
     * @return  void
     * @since   1.0.0
     */
    private function generateMetaDescription(object $articleObject, string $sourceText): void
    {
        // 1. Remove HTML tags to get plain text.
        $plainText = strip_tags($sourceText);

        // 2. Decode HTML entities (e.g., & to &) for cleaner text.
        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');

        // 3. Process whitespace (multiple spaces/newlines to a single space).
        $plainText = trim(preg_replace('/\s+/', ' ', $plainText));

        // 4. Truncate the text to the desired meta description length.
        $truncatedText = HTMLHelper::_('string.truncate', $plainText, $this->metaDescLength);

        if (!empty($truncatedText)) {
            $articleObject->metadesc = $truncatedText;
        }
    }

    /**
     * Generates and sets the meta keywords for the article.
     *
     * @param   object  $articleObject  The article data object.
     * @param   string  $sourceText     The article's text content.
     * @return  void
     * @since   1.0.0
     */
    private function generateMetaKeywords(object $articleObject, string $sourceText): void
    {
        // 1. Remove HTML tags.
        $cleanedText = strip_tags($sourceText);

        // 2. Convert to lowercase for consistent word matching.
        $cleanedText = mb_strtolower($cleanedText, 'UTF-8');

        // 3. Pre-process text: remove punctuation, leaving only letters, numbers, and spaces.
        $cleanedText = preg_replace('/[^\p{L}\p{N}\s]+/u', '', $cleanedText);

        // 4. Split the cleaned text into an array of words.
        $words = explode(' ', $cleanedText);

        if (empty($words)) {
            return;
        }

        // 5. Filter words: remove stop words and words shorter than minKeywordLength.
        $filteredWords = [];
        foreach ($words as $word) {
            if (mb_strlen($word, 'UTF-8') >= $this->minKeywordLength && !in_array($word, $this->stopWords, true)) {
                $filteredWords[] = $word;
            }
        }

        if (empty($filteredWords)) {
            return;
        }

        // 6. Count frequencies of the remaining words.
        $wordFrequencies = array_count_values($filteredWords);

        // 7. Sort words by frequency (most frequent first).
        arsort($wordFrequencies);

        // 8. Select the top N most frequent words as keywords.
        $topKeywords = array_slice(array_keys($wordFrequencies), 0, $this->maxKeywordsCount);

        if (!empty($topKeywords)) {
            $articleObject->metakey = implode(', ', $topKeywords);
        }
    }
}
