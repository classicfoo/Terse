<?php
/**
 * Simple Markdown to HTML converter.
 * Supports a small subset of Markdown:
 * - Headings (# through ######)
 * - Emphasis (*, _, **, __)
 * - Inline code (`code`)
 * - Links [text](url)
 * - Unordered lists starting with - or *
 * - Ordered lists starting with 1. 2. etc
 * - Paragraphs separated by blank lines
 *
 * HTML in the source text is escaped to prevent XSS.
 */
function render_markdown(string $text): string {
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $lines = explode("\n", trim($text));
    $html = '';
    $listType = null;
    $paragraph = '';

    $flushParagraph = function () use (&$html, &$paragraph) {
        if ($paragraph !== '') {
            $html .= '<p>' . render_markdown_inline(trim($paragraph)) . "</p>\n";
            $paragraph = '';
        }
    };

    $closeList = function () use (&$html, &$listType) {
        if ($listType) {
            $html .= '</' . $listType . ">\n";
            $listType = null;
        }
    };

    foreach ($lines as $line) {
        if (preg_match('/^#{1,6} /', $line)) {
            $closeList();
            $flushParagraph();
            $level = strspn($line, '#');
            $content = trim(substr($line, $level));
            $html .= '<h' . $level . '>' . render_markdown_inline($content) . '</h' . $level . ">\n";
        } elseif (preg_match('/^(?:\-|\*)\s+/', $line)) {
            $flushParagraph();
            if ($listType !== 'ul') {
                $closeList();
                $html .= "<ul>\n";
                $listType = 'ul';
            }
            $item = preg_replace('/^(?:\-|\*)\s+/', '', $line);
            $html .= '<li>' . render_markdown_inline($item) . "</li>\n";
        } elseif (preg_match('/^\d+\.\s+/', $line)) {
            $flushParagraph();
            if ($listType !== 'ol') {
                $closeList();
                $html .= "<ol>\n";
                $listType = 'ol';
            }
            $item = preg_replace('/^\d+\.\s+/', '', $line);
            $html .= '<li>' . render_markdown_inline($item) . "</li>\n";
        } elseif (trim($line) === '') {
            $closeList();
            $flushParagraph();
        } else {
            $closeList();
            if ($paragraph !== '') {
                $paragraph .= ' ';
            }
            $paragraph .= $line;
        }
    }

    $closeList();
    $flushParagraph();
    return $html;
}

function render_markdown_inline(string $text): string {
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    // code
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    // strong
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $text);
    // emphasis
    $text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);
    $text = preg_replace('/_(.+?)_/s', '<em>$1</em>', $text);
    // links
    $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $text);
    return $text;
}
