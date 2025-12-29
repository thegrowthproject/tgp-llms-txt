# WP LLMs.txt v1.1.0 Upgrade Plans

Two independent improvements for the Gutenberg block. Can be implemented separately or together.

**Target:** WordPress 6.5+ (drops support for older versions)

---

## Plan A: Native WordPress Styling Controls

**Goal:** Replace custom color attributes with WordPress Block Supports API for theme-integrated styling.

### Current State

```
blocks/llm-buttons/
├── block.json          # 8 custom attributes for colors/labels
├── index.js            # Manual PanelColorSettings controls
├── render.php          # Inline styles from attributes
├── style.css           # Custom button styles
└── editor.css          # Duplicated styles
```

**Problems:**
- Custom color pickers disconnected from theme palette
- No theme.json integration
- No global styles support
- Duplicated CSS between editor and frontend
- Manual inline styles in render.php

### Target State

```
blocks/llm-buttons/
├── block.json          # Block Supports for colors, typography, spacing
├── index.js            # Minimal - just labels and layout controls
├── render.php          # Uses wp-element-button class, no inline styles
└── style.css           # Minimal overrides only
```

**Benefits:**
- Colors from theme palette
- Inherits theme.json button styles
- Works with global styles
- Dark mode support (if theme provides)
- Less code to maintain

---

### Implementation Steps

#### Step 1: Update block.json

**File:** `blocks/llm-buttons/block.json`

Remove custom color attributes, add Block Supports:

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "tgp/llm-buttons",
  "version": "1.1.0",
  "title": "LLM Buttons",
  "category": "widgets",
  "description": "Add Copy for LLM and View as Markdown buttons to your content.",
  "keywords": ["llm", "markdown", "copy", "ai", "chatgpt"],
  "textdomain": "tgp-llms-txt",
  "attributes": {
    "showIcons": {
      "type": "boolean",
      "default": true
    },
    "layout": {
      "type": "string",
      "default": "row",
      "enum": ["row", "stack"]
    },
    "copyButtonLabel": {
      "type": "string",
      "default": "Copy for LLM"
    },
    "viewButtonLabel": {
      "type": "string",
      "default": "View as Markdown"
    }
  },
  "supports": {
    "html": false,
    "align": ["left", "center", "right", "wide"],
    "spacing": {
      "margin": true,
      "padding": true,
      "blockGap": true
    },
    "color": {
      "background": true,
      "text": true,
      "link": true,
      "gradients": true,
      "__experimentalDefaultControls": {
        "background": true,
        "text": true
      }
    },
    "typography": {
      "fontSize": true,
      "fontWeight": true,
      "__experimentalFontFamily": true,
      "__experimentalDefaultControls": {
        "fontSize": true
      }
    },
    "__experimentalBorder": {
      "radius": true,
      "width": true,
      "color": true,
      "style": true,
      "__experimentalDefaultControls": {
        "radius": true
      }
    }
  },
  "editorScript": "file:./index.js",
  "style": "file:./style.css",
  "render": "file:./render.php",
  "viewScript": "file:./view.js"
}
```

**Changes:**
- Removed: `copyButtonBgColor`, `copyButtonTextColor`, `viewButtonBgColor`, `viewButtonTextColor`
- Added: `color`, `typography`, `__experimentalBorder` supports
- Added: `blockGap` for button spacing
- Removed: `editorStyle` (no longer needed)

---

#### Step 2: Simplify index.js

**File:** `blocks/llm-buttons/index.js`

Remove PanelColorSettings, keep only custom controls:

```js
(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { useBlockProps, InspectorControls } = wp.blockEditor;
  const { PanelBody, ToggleControl, SelectControl, TextControl } = wp.components;
  const { __ } = wp.i18n;
  const { createElement: el, Fragment } = wp.element;

  // Icons
  const copyIcon = el('svg', {
    xmlns: 'http://www.w3.org/2000/svg',
    width: 16,
    height: 16,
    viewBox: '0 0 24 24',
    fill: 'none',
    stroke: 'currentColor',
    strokeWidth: 2,
    strokeLinecap: 'round',
    strokeLinejoin: 'round'
  },
    el('rect', { x: 9, y: 9, width: 13, height: 13, rx: 2, ry: 2 }),
    el('path', { d: 'M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1' })
  );

  const viewIcon = el('svg', {
    xmlns: 'http://www.w3.org/2000/svg',
    width: 16,
    height: 16,
    viewBox: '0 0 24 24',
    fill: 'none',
    stroke: 'currentColor',
    strokeWidth: 2,
    strokeLinecap: 'round',
    strokeLinejoin: 'round'
  },
    el('path', { d: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z' }),
    el('polyline', { points: '14 2 14 8 20 8' }),
    el('line', { x1: 16, y1: 13, x2: 8, y2: 13 }),
    el('line', { x1: 16, y1: 17, x2: 8, y2: 17 }),
    el('polyline', { points: '10 9 9 9 8 9' })
  );

  const blockIcon = el('svg', {
    xmlns: 'http://www.w3.org/2000/svg',
    width: 24,
    height: 24,
    viewBox: '0 0 24 24',
    fill: 'none',
    stroke: 'currentColor',
    strokeWidth: 2
  },
    el('rect', { x: 9, y: 9, width: 13, height: 13, rx: 2, ry: 2 }),
    el('path', { d: 'M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1' })
  );

  registerBlockType('tgp/llm-buttons', {
    icon: blockIcon,

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const { showIcons, layout, copyButtonLabel, viewButtonLabel } = attributes;

      const blockProps = useBlockProps({
        className: 'wp-block-tgp-llm-buttons ' + (layout === 'stack' ? 'is-layout-stack' : 'is-layout-row')
      });

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, {
            title: __('Button Settings', 'tgp-llms-txt'),
            initialOpen: true
          },
            el(SelectControl, {
              label: __('Layout', 'tgp-llms-txt'),
              value: layout,
              options: [
                { label: __('Row', 'tgp-llms-txt'), value: 'row' },
                { label: __('Stack', 'tgp-llms-txt'), value: 'stack' }
              ],
              onChange: (value) => setAttributes({ layout: value })
            }),
            el(ToggleControl, {
              label: __('Show Icons', 'tgp-llms-txt'),
              checked: showIcons,
              onChange: (value) => setAttributes({ showIcons: value })
            }),
            el(TextControl, {
              label: __('Copy Button Label', 'tgp-llms-txt'),
              value: copyButtonLabel,
              onChange: (value) => setAttributes({ copyButtonLabel: value })
            }),
            el(TextControl, {
              label: __('View Button Label', 'tgp-llms-txt'),
              value: viewButtonLabel,
              onChange: (value) => setAttributes({ viewButtonLabel: value })
            })
          )
        ),

        // Block Preview - uses wp-element-button for theme styling
        el('div', blockProps,
          el('button', {
            type: 'button',
            className: 'wp-element-button tgp-llm-btn tgp-copy-btn'
          },
            showIcons && el('span', { className: 'tgp-btn-icon' }, copyIcon),
            el('span', { className: 'tgp-btn-text' }, copyButtonLabel)
          ),
          el('a', {
            href: '#',
            className: 'wp-element-button tgp-llm-btn tgp-view-btn',
            onClick: (e) => e.preventDefault()
          },
            showIcons && el('span', { className: 'tgp-btn-icon' }, viewIcon),
            el('span', { className: 'tgp-btn-text' }, viewButtonLabel)
          )
        )
      );
    },

    save: function () {
      return null;
    }
  });
})(window.wp);
```

**Changes:**
- Removed: `PanelColorSettings` imports and usage
- Removed: Color-related attribute destructuring
- Removed: Inline `style` props on buttons
- Added: `wp-element-button` class to buttons
- Simplified: Overall code reduced by ~40%

---

#### Step 3: Update render.php

**File:** `blocks/llm-buttons/render.php`

```php
<?php
/**
 * Server-side rendering for LLM Buttons block.
 *
 * @package TGP_LLMs_Txt
 */

global $post;
if ( ! $post ) {
  return '';
}

// Get attributes with defaults.
$show_icons = $attributes['showIcons'] ?? true;
$layout     = $attributes['layout'] ?? 'row';
$copy_label = $attributes['copyButtonLabel'] ?? 'Copy for LLM';
$view_label = $attributes['viewButtonLabel'] ?? 'View as Markdown';

// Build markdown URL.
$permalink = get_permalink( $post );
$md_url    = rtrim( $permalink, '/' ) . '.md';

// Icons SVG.
$copy_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
$view_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';

// Wrapper attributes (includes Block Supports styles automatically).
$wrapper_attributes = get_block_wrapper_attributes(
  [
    'class' => 'wp-block-tgp-llm-buttons ' . ( 'stack' === $layout ? 'is-layout-stack' : 'is-layout-row' ),
  ]
);
?>
<div <?php echo wp_kses_post( $wrapper_attributes ); ?>>
  <button
    type="button"
    class="wp-element-button tgp-llm-btn tgp-copy-btn"
    data-post-id="<?php echo esc_attr( $post->ID ); ?>"
    title="Copy this content in markdown format for AI assistants"
  >
    <?php if ( $show_icons ) : ?>
      <span class="tgp-btn-icon"><?php echo wp_kses_post( $copy_icon ); ?></span>
    <?php endif; ?>
    <span class="tgp-btn-text"><?php echo esc_html( $copy_label ); ?></span>
  </button>
  <a
    href="<?php echo esc_url( $md_url ); ?>"
    target="_blank"
    rel="noopener noreferrer"
    class="wp-element-button tgp-llm-btn tgp-view-btn"
    title="View this content as plain markdown"
  >
    <?php if ( $show_icons ) : ?>
      <span class="tgp-btn-icon"><?php echo wp_kses_post( $view_icon ); ?></span>
    <?php endif; ?>
    <span class="tgp-btn-text"><?php echo esc_html( $view_label ); ?></span>
  </a>
</div>
```

**Changes:**
- Removed: All inline `style` attributes
- Removed: Color variable extraction
- Added: `wp-element-button` class (inherits theme button styles)
- Simplified: Wrapper attributes automatically include Block Supports styles

---

#### Step 4: Simplify style.css

**File:** `blocks/llm-buttons/style.css`

```css
/**
 * LLM Buttons Block - Styles
 *
 * Uses wp-element-button for base styling from theme.
 * Only overrides specific layout and icon behavior.
 */

.wp-block-tgp-llm-buttons {
  display: flex;
  flex-wrap: wrap;
}

.wp-block-tgp-llm-buttons.is-layout-row {
  flex-direction: row;
  align-items: center;
}

.wp-block-tgp-llm-buttons.is-layout-stack {
  flex-direction: column;
  align-items: stretch;
}

.wp-block-tgp-llm-buttons.aligncenter {
  justify-content: center;
}

.wp-block-tgp-llm-buttons.alignright {
  justify-content: flex-end;
}

/* Button adjustments */
.wp-block-tgp-llm-buttons .tgp-llm-btn {
  display: inline-flex;
  align-items: center;
  text-decoration: none;
}

.wp-block-tgp-llm-buttons .tgp-llm-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Icons */
.wp-block-tgp-llm-buttons .tgp-btn-icon {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  margin-right: 0.5em;
}

.wp-block-tgp-llm-buttons .tgp-btn-icon svg {
  width: 1em;
  height: 1em;
}

.wp-block-tgp-llm-buttons .tgp-btn-text {
  white-space: nowrap;
}

/* Responsive */
@media (max-width: 480px) {
  .wp-block-tgp-llm-buttons.is-layout-row {
    flex-direction: column;
    align-items: stretch;
  }

  .wp-block-tgp-llm-buttons .tgp-llm-btn {
    justify-content: center;
  }
}
```

**Changes:**
- Removed: All color, font-size, padding, border-radius definitions (now from theme)
- Removed: Transition/hover states (inherited from wp-element-button)
- Kept: Layout (flex direction), icon sizing, responsive behavior
- Reduced: From ~83 lines to ~60 lines

---

#### Step 5: Delete editor.css

**File:** `blocks/llm-buttons/editor.css`

Delete this file entirely. Block Supports handle editor styling automatically.

---

#### Step 6: Update main plugin file

**File:** `tgp-llms-txt.php`

No changes needed. The `register_block_type()` call reads from block.json automatically.

---

### Migration Notes

**Breaking change:** Existing blocks will lose their custom color settings. Colors will reset to theme defaults.

**Mitigation:** Add a migration notice in the block editor:

```js
// In index.js, add deprecated version
deprecated: [
  {
    attributes: {
      copyButtonBgColor: { type: 'string', default: '#1e1e1e' },
      // ... old attributes
    },
    migrate(attributes) {
      // Return only the attributes we're keeping
      return {
        showIcons: attributes.showIcons,
        layout: attributes.layout,
        copyButtonLabel: attributes.copyButtonLabel,
        viewButtonLabel: attributes.viewButtonLabel,
      };
    },
    save() {
      return null;
    }
  }
]
```

---

### Testing Checklist

- [ ] Block renders in editor with theme button styles
- [ ] Color controls appear in Block settings sidebar
- [ ] Typography controls appear in Block settings sidebar
- [ ] Border radius control works
- [ ] Spacing (gap between buttons) control works
- [ ] Layout toggle (row/stack) works
- [ ] Icon toggle works
- [ ] Label customization works
- [ ] Frontend renders with theme button styles
- [ ] Copy button still functional (test in Plan B)
- [ ] View button opens .md URL
- [ ] Responsive stacking works on mobile
- [ ] Existing blocks migrate without errors

---

## Plan B: Interactivity API + Fetch .md Endpoint

**Goal:** Replace AJAX + custom JavaScript with WordPress Interactivity API for reactive copy functionality.

### Current State

```
AJAX Flow:
User clicks → view.js listener → fetch to admin-ajax.php → PHP generates markdown → JSON response → clipboard API
```

**Problems:**
- `admin-ajax.php` is slow (full WP bootstrap)
- Manual event listeners
- Manual state management
- Separate JS file to maintain

### Target State

```
Interactivity API Flow:
User clicks → data-wp-on--click → fetch to .md endpoint → clipboard API
```

**Benefits:**
- Uses existing .md endpoint (already cached)
- Declarative state management
- No admin-ajax.php overhead
- Standard WordPress patterns
- Smaller, more maintainable code

---

### Implementation Steps

#### Step 1: Update block.json for Interactivity API

**File:** `blocks/llm-buttons/block.json`

Add Interactivity API support:

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "tgp/llm-buttons",
  "version": "1.1.0",
  "title": "LLM Buttons",
  "category": "widgets",
  "description": "Add Copy for LLM and View as Markdown buttons to your content.",
  "keywords": ["llm", "markdown", "copy", "ai", "chatgpt"],
  "textdomain": "tgp-llms-txt",
  "attributes": {
    "showIcons": {
      "type": "boolean",
      "default": true
    },
    "layout": {
      "type": "string",
      "default": "row",
      "enum": ["row", "stack"]
    },
    "copyButtonLabel": {
      "type": "string",
      "default": "Copy for LLM"
    },
    "viewButtonLabel": {
      "type": "string",
      "default": "View as Markdown"
    }
  },
  "supports": {
    "html": false,
    "align": ["left", "center", "right", "wide"],
    "spacing": {
      "margin": true,
      "padding": true,
      "blockGap": true
    },
    "color": {
      "background": true,
      "text": true,
      "link": true,
      "gradients": true
    },
    "typography": {
      "fontSize": true,
      "fontWeight": true
    },
    "__experimentalBorder": {
      "radius": true,
      "width": true,
      "color": true
    },
    "interactivity": true
  },
  "editorScript": "file:./index.js",
  "style": "file:./style.css",
  "render": "file:./render.php",
  "viewScriptModule": "file:./view.js"
}
```

**Changes:**
- Added: `"interactivity": true` in supports
- Changed: `"viewScript"` to `"viewScriptModule"` (ES modules for Interactivity API)

---

#### Step 2: Rewrite view.js with Interactivity API

**File:** `blocks/llm-buttons/view.js`

```js
/**
 * LLM Buttons Block - Frontend Interactivity
 *
 * Uses WordPress Interactivity API for reactive state management.
 * Fetches markdown from the .md endpoint (not admin-ajax.php).
 */

import { store, getContext } from '@wordpress/interactivity';

const { state, actions } = store('tgp/llm-buttons', {
  state: {
    get buttonText() {
      const ctx = getContext();
      switch (ctx.copyState) {
        case 'loading':
          return ctx.loadingText;
        case 'success':
          return ctx.successText;
        case 'error':
          return ctx.errorText;
        default:
          return ctx.defaultText;
      }
    },
    get isLoading() {
      return getContext().copyState === 'loading';
    },
    get isDisabled() {
      const ctx = getContext();
      return ctx.copyState === 'loading' || ctx.copyState === 'success';
    }
  },
  actions: {
    *copyMarkdown() {
      const ctx = getContext();

      // Prevent double-clicks
      if (ctx.copyState === 'loading') {
        return;
      }

      ctx.copyState = 'loading';

      try {
        // Fetch from .md endpoint (not admin-ajax.php)
        const response = yield fetch(ctx.mdUrl);

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const markdown = yield response.text();

        // Copy to clipboard
        yield navigator.clipboard.writeText(markdown);

        ctx.copyState = 'success';

        // Reset after 2 seconds
        yield new Promise(resolve => setTimeout(resolve, 2000));
        ctx.copyState = 'idle';

      } catch (error) {
        console.error('Copy failed:', error);
        ctx.copyState = 'error';

        // Reset after 2 seconds
        yield new Promise(resolve => setTimeout(resolve, 2000));
        ctx.copyState = 'idle';
      }
    }
  }
});
```

**Changes:**
- Complete rewrite using `@wordpress/interactivity`
- Uses generator functions (`*copyMarkdown`) for async operations
- Fetches from `.md` endpoint directly (no AJAX)
- Declarative state: `copyState` drives button text and disabled state

---

#### Step 3: Update render.php with Interactivity directives

**File:** `blocks/llm-buttons/render.php`

```php
<?php
/**
 * Server-side rendering for LLM Buttons block.
 *
 * Uses WordPress Interactivity API for frontend reactivity.
 *
 * @package TGP_LLMs_Txt
 */

global $post;
if ( ! $post ) {
  return '';
}

// Get attributes with defaults.
$show_icons = $attributes['showIcons'] ?? true;
$layout     = $attributes['layout'] ?? 'row';
$copy_label = $attributes['copyButtonLabel'] ?? 'Copy for LLM';
$view_label = $attributes['viewButtonLabel'] ?? 'View as Markdown';

// Build markdown URL.
$permalink = get_permalink( $post );
$md_url    = rtrim( $permalink, '/' ) . '.md';

// Icons SVG.
$copy_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
$view_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';

// Interactivity API context.
$context = [
  'mdUrl'       => $md_url,
  'copyState'   => 'idle',
  'defaultText' => $copy_label,
  'loadingText' => __( 'Copying...', 'tgp-llms-txt' ),
  'successText' => __( 'Copied!', 'tgp-llms-txt' ),
  'errorText'   => __( 'Failed', 'tgp-llms-txt' ),
];

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
  [
    'class' => 'wp-block-tgp-llm-buttons ' . ( 'stack' === $layout ? 'is-layout-stack' : 'is-layout-row' ),
  ]
);
?>
<div
  <?php echo wp_kses_post( $wrapper_attributes ); ?>
  data-wp-interactive="tgp/llm-buttons"
  <?php echo wp_interactivity_data_wp_context( $context ); ?>
>
  <button
    type="button"
    class="wp-element-button tgp-llm-btn tgp-copy-btn"
    title="<?php esc_attr_e( 'Copy this content in markdown format for AI assistants', 'tgp-llms-txt' ); ?>"
    data-wp-on--click="actions.copyMarkdown"
    data-wp-bind--disabled="state.isDisabled"
    data-wp-class--is-loading="state.isLoading"
  >
    <?php if ( $show_icons ) : ?>
      <span class="tgp-btn-icon"><?php echo wp_kses_post( $copy_icon ); ?></span>
    <?php endif; ?>
    <span class="tgp-btn-text" data-wp-text="state.buttonText"><?php echo esc_html( $copy_label ); ?></span>
  </button>
  <a
    href="<?php echo esc_url( $md_url ); ?>"
    target="_blank"
    rel="noopener noreferrer"
    class="wp-element-button tgp-llm-btn tgp-view-btn"
    title="<?php esc_attr_e( 'View this content as plain markdown', 'tgp-llms-txt' ); ?>"
  >
    <?php if ( $show_icons ) : ?>
      <span class="tgp-btn-icon"><?php echo wp_kses_post( $view_icon ); ?></span>
    <?php endif; ?>
    <span class="tgp-btn-text"><?php echo esc_html( $view_label ); ?></span>
  </a>
</div>
```

**Key Interactivity API directives:**

| Directive | Purpose |
|-----------|---------|
| `data-wp-interactive="tgp/llm-buttons"` | Registers block with store namespace |
| `data-wp-context` | Passes server-side data (mdUrl, labels) to client |
| `data-wp-on--click="actions.copyMarkdown"` | Click handler |
| `data-wp-bind--disabled="state.isDisabled"` | Reactive disabled attribute |
| `data-wp-class--is-loading="state.isLoading"` | Reactive class toggle |
| `data-wp-text="state.buttonText"` | Reactive text content |

---

#### Step 4: Update style.css for loading state

**File:** `blocks/llm-buttons/style.css`

Add loading state style:

```css
/* Add to existing styles */

.wp-block-tgp-llm-buttons .tgp-copy-btn.is-loading {
  opacity: 0.7;
  cursor: wait;
}

.wp-block-tgp-llm-buttons .tgp-copy-btn.is-loading .tgp-btn-icon {
  animation: tgp-spin 1s linear infinite;
}

@keyframes tgp-spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
```

---

#### Step 5: Remove AJAX handler

**File:** `includes/class-ui-buttons.php`

This class is no longer needed. Delete it entirely.

---

#### Step 6: Update main plugin file

**File:** `tgp-llms-txt.php`

Remove UI buttons class and AJAX localization:

```php
<?php
/**
 * Plugin Name: TGP LLMs.txt
 * Version: 1.1.0
 * Requires at least: 6.5
 * ...
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

define( 'TGP_LLMS_VERSION', '1.1.0' );
define( 'TGP_LLMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TGP_LLMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

class TGP_LLMs_Txt {

  private static $instance = null;

  public static function get_instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct() {
    $this->load_dependencies();
    $this->init_hooks();
  }

  private function load_dependencies() {
    require_once TGP_LLMS_PLUGIN_DIR . 'includes/class-markdown-converter.php';
    require_once TGP_LLMS_PLUGIN_DIR . 'includes/class-frontmatter.php';
    require_once TGP_LLMS_PLUGIN_DIR . 'includes/class-endpoint-handler.php';
    require_once TGP_LLMS_PLUGIN_DIR . 'includes/class-llms-txt-generator.php';
    // REMOVED: class-ui-buttons.php
  }

  private function init_hooks() {
    new TGP_Endpoint_Handler();
    new TGP_LLMs_Txt_Generator();
    // REMOVED: new TGP_UI_Buttons();

    add_action( 'init', [ $this, 'register_blocks' ] );
    register_activation_hook( __FILE__, [ $this, 'activate' ] );
    register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
  }

  public function register_blocks() {
    register_block_type( TGP_LLMS_PLUGIN_DIR . 'blocks/llm-buttons' );
    // REMOVED: wp_localize_script for AJAX
  }

  public function activate() {
    flush_rewrite_rules();
  }

  public function deactivate() {
    flush_rewrite_rules();
  }
}

function tgp_llms_txt_init() {
  return TGP_LLMs_Txt::get_instance();
}
add_action( 'plugins_loaded', 'tgp_llms_txt_init' );
```

**Changes:**
- Removed: `require_once` for class-ui-buttons.php
- Removed: `new TGP_UI_Buttons()` instantiation
- Removed: `wp_localize_script()` call
- Updated: Version to 1.1.0
- Added: `Requires at least: 6.5`

---

#### Step 7: Delete deprecated files

Delete these files:
- `includes/class-ui-buttons.php`

---

### Files Changed Summary

| File | Action | Description |
|------|--------|-------------|
| `block.json` | Modified | Added `interactivity` support, changed to `viewScriptModule` |
| `view.js` | Rewritten | Interactivity API store with fetch to .md |
| `render.php` | Modified | Added `data-wp-*` directives |
| `style.css` | Modified | Added loading state styles |
| `tgp-llms-txt.php` | Modified | Removed AJAX setup, bumped version |
| `includes/class-ui-buttons.php` | Deleted | No longer needed |

---

### Testing Checklist

- [ ] Click "Copy for LLM" shows "Copying..." state
- [ ] Button is disabled during copy operation
- [ ] Successful copy shows "Copied!" for 2 seconds
- [ ] Failed copy shows "Failed" for 2 seconds
- [ ] Button resets to original text after state clears
- [ ] Clipboard contains valid markdown
- [ ] Network tab shows fetch to .md endpoint (not admin-ajax.php)
- [ ] No JavaScript errors in console
- [ ] Works on single posts
- [ ] Works on pages
- [ ] Multiple blocks on same page work independently
- [ ] "View as Markdown" link still opens .md URL

---

## Combined Implementation Order

If implementing both plans together:

1. **Plan A first** - Update styling to Block Supports
   - Less risk of breaking copy functionality
   - Can test styling changes independently

2. **Plan B second** - Migrate to Interactivity API
   - Depends on updated block.json from Plan A
   - More significant change to test

3. **Final cleanup**
   - Delete editor.css
   - Delete class-ui-buttons.php
   - Update README.md
   - Update CHANGELOG.md
   - Bump version to 1.1.0

---

## Version Requirements

After both plans:

- **WordPress:** 6.5+ (Interactivity API)
- **PHP:** 8.2+

Update `readme.txt` (if creating for wp.org):

```
Requires at least: 6.5
Tested up to: 6.7
Requires PHP: 8.2
```
