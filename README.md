# Content - Auto Meta Tags Joomla! Plugin

A Joomla! 5 content plugin designed to automatically generate and fill meta description and meta keywords for your articles if they are not already set. This helps improve SEO by ensuring all articles have relevant metadata.

## Features

*   **Automatic Meta Description Generation:**
    *   Checks if an article's meta description field is empty.
    *   If empty, it extracts content from the article's intro text (if available) or the beginning of the full article text.
    *   The extracted text is cleaned (HTML removed, whitespace normalized) and truncated to an optimal length for search engines.
*   **Automatic Meta Keywords Generation:**
    *   Checks if an article's meta keywords field is empty.
    *   If empty, it performs a word frequency analysis on the entire article content (intro text and full text).
    *   Identifies the most frequently occurring words, filtering out common "stop words" and short words.
    *   Saves these top words as comma-separated meta keywords.
*   **Seamless Integration:** Works automatically in the background. Once installed and configured, it processes articles when they are saved.
*   **Configurable:** Offers settings to configure its behavior.

## Installation

1.  Download the plugin package.
2.  In your Joomla Administrator backend, navigate to **System** -> **Install** -> **Extensions**.
3.  Upload the downloaded ZIP file.
4.  Once installed, navigate to **System** -> **Manage** -> **Plugins**.
5.  Search for "Content - Auto Meta Tags".
6.  Enable the plugin by clicking the status icon.

## Configuration

You can customize the plugin's behavior by accessing its settings:
1.  In the Plugin Manager (**System** -> **Manage** -> **Plugins**), find and click on "Content - Auto Meta Tags".
2.  You will find the following options under the "Basic Settings" tab:
    *   **Meta Description Length:** (Default: 155) Set the desired maximum length for the auto-generated meta description.
    *   **Max Keywords Count:** (Default: 7) Define the maximum number of keywords to be generated.
    *   **Min Keyword Length:** (Default: 3) Set the minimum length for a word to be considered a potential keyword.
    *   **Stop Words List:** (Default: a, an, and, ...) Provide a comma-separated list of common words (e.g., "the", "is", "a") that should be excluded from keyword generation.
