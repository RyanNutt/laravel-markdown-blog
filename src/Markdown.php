<?php

namespace Aelora\MarkdownBlog;

use Illuminate\Support\Arr;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DescriptionList\DescriptionListExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Mention\MentionExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Class that handles working with the markdown files and converting them
 * as needed.
 */
class Markdown
{

    /**
     * Parses the markdown and returns it as HTML
     */
    public function convert(string $markdown): string
    {
        // $converter = $this->converter();
        // ray($converter);
        // return '';
        return $this->converter()->convert($markdown);
    }

    /**
     * Returns a CommonMark converter pulling settings and extensions
     * from config file. 
     */
    public function converter(): MarkdownConverter
    {

        $config = config('mdblog.markdown', []);

        $extensions = [];
        $extensions[] = new CommonMarkCoreExtension();

        // unset($config['gfm']);
        // unset($config['attributes']);
        // unset($config['autolink']);
        // unset($config['descriptionList']);
        // unset($config['disallowedRawHtml']);
        // unset($config['strikethrough']);

        if (Arr::get($config, 'gfm', false)) {
            $extensions[] = new GithubFlavoredMarkdownExtension();
        }
        Arr::forget($config, 'gfm');

        if (Arr::get($config, 'attributes', true)) {
            $extensions[] = new AttributesExtension();
        }
        Arr::forget($config, 'attributes');

        if (Arr::get('autolink', false)) {
            $extensions[] = new AutolinkExtension();
        }
        Arr::forget($config, 'autolink');

        if (Arr::get($config, 'description_list', false)) {
            $extensions[] = new DescriptionListExtension();
        }
        Arr::forget($config, 'description_list');

        if (Arr::get($config, 'strikethrough', true)) {
            $extensions[] = new StrikethroughExtension();
        }
        Arr::forget($config, 'strikethrough');

        if (Arr::get($config, 'disallowed_raw_html', false) !== false) {
            $extensions[] = new DisallowedRawHtmlExtension();
        } else {
            Arr::forget($config, 'disallowed_raw_html');
        }

        if (is_array(Arr::get($config, 'external_link', false))) {
            $extensions[] = new ExternalLinkExtension();
        } else {
            Arr::forget($config, 'external_link');
        }

        if (is_array(Arr::get($config, 'footnote', false))) {
            $extensions[] = new FootnoteExtension();
        } else {
            Arr::forget($config, 'footnote');
        }

        if (is_array(Arr::get($config, 'heading_permalink', false))) {
            $extensions[] = new HeadingPermalinkExtension();
        } else {
            Arr::forget($config, 'heading_permalink');
        }
        if (is_array(Arr::get($config, 'mentions', false))) {
            $extensions[] = new MentionExtension();
        } else {
            Arr::forget($config, 'mentions');
        }

        if (is_array(Arr::get($config, 'smartpunct', false))) {
            $extensions[] = new SmartPunctExtension();
        } else {
            Arr::forget($config, 'smartpunct');
        }

        if (is_array(Arr::get($config, 'table_of_contents', false))) {
            $extensions[] = new TableOfContentsExtension();
        } else {
            Arr::forget($config, 'table');
        }

        if (is_array(Arr::get($config, 'table', false))) {
            $extensions[] = new TableExtension();
        } else {
            Arr::forget($config, 'table');
        }

        $env = new Environment($config);
        foreach ($extensions as $ext) {
            $env->addExtension($ext);
        }

        return new MarkdownConverter($env);
    }

    /**
     * Update the config for the MarkdownConverter. This must be used
     * before calling ->converter() for the settings to take. 
     * 
     * This is calling the built in config() method in Laravel with 
     * a prefix of 'mdblog.' to target the right settings. 
     * 
     * Note that this doesn't do any validation of the values passed. 
     * 
     * This also does not save the new values in the config file. It 
     * only updates for the current page view. 
     */
    public function config($k, $v = null): void
    {
        if (is_string($k)) {
            config('mdblog.' . $k, $v);
        } elseif (is_array($k)) {
            foreach ($k as $key => $value) {
                config('mdblog.' . $key, $value);
            }
        }
    }
}
