# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2025-12-31

### Changed
- **BREAKING:** Requires WordPress 6.5+ (was 5.0+)
- Migrated copy button to WordPress Interactivity API for reactive state management
- Copy button now fetches directly from `.md` endpoint (faster, cached) instead of admin-ajax.php
- Replaced custom color/style attributes with WordPress Block Supports API
- Buttons now inherit theme styles automatically (Brand, Dark, Light, Tint variants)
- Split `llm-buttons` block into separate `copy-button` and `view-button` blocks

### Removed
- `includes/class-ui-buttons.php` (AJAX handler no longer needed)
- `blocks/llm-buttons/editor.css` (styles now from Block Supports)
- Custom `backgroundColor`, `textColor`, `borderRadius` attributes (replaced by Block Supports)

### Fixed
- Button styles now match theme design system
- Improved performance by eliminating admin-ajax.php overhead

## [1.0.0] - 2025-12-30

### Added
- `/llms.txt` endpoint with site index following llmstxt.org specification
- `.md` endpoints for individual pages and posts
- Gutenberg block for copy-to-clipboard functionality
- YAML frontmatter for markdown files (title, description, date, author, tags)
- Gutenberg to markdown conversion (headings, lists, tables, links, emphasis, code)
- Cache headers for performance (1 hour)

### Developer Experience
- WordPress Coding Standards (PHPCS) with CI
- GitHub Actions for linting and releases
- Automated plugin zip builds on release
- Issue and PR templates
- Contributing guidelines

### Requirements
- WordPress 5.0+ (Gutenberg)
- PHP 8.2+
