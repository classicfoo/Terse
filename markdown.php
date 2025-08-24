<?php
/**
 * Simple Markdown to HTML converter.
 * Supports a small subset of Markdown:
 * - Headings (# through ######)
 * - Emphasis (*, _, **, __)
 * - Inline code (`code`)
 * - Links [text](url)
 * - Unordered lists starting with - or *
 * - Paragraphs separated by blank lines
 *
 * HTML in the source text is escaped to prevent XSS.
 */
function render_markdown(string $text): string {
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $blocks = preg_split('/\n{2,}/', trim($text));
    $html = '';
    foreach ($blocks as $block) {
        if (preg_match('/^#{1,6} /', $block)) {
            $level = strspn($block, '#');
            $content = trim(substr($block, $level));
            $html .= '<h' . $level . '>' . render_markdown_inline($content) . '</h' . $level . ">\n";
        } elseif (preg_match('/^(?:\-|\*) /m', $block)) {
            $html .= "<ul>\n";
            $lines = explode("\n", $block);
            foreach ($lines as $line) {
                $item = preg_replace('/^(?:\-|\*)\s+/', '', $line);
                $html .= '<li>' . render_markdown_inline($item) . "</li>\n";
            }
            $html .= "</ul>\n";
        } else {
            $html .= '<p>' . render_markdown_inline($block) . "</p>\n";
        }
    }
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
