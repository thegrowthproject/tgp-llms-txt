# /new-block

Scaffold a new WordPress Gutenberg block with all patterns baked in.

## Usage

```
/new-block [block-name]
```

## Description

Creates a new block in `blocks/{block-name}/` with all necessary files following the patterns established in this plugin. The skill will prompt for configuration options and generate files accordingly.

## Workflow

1. **Get block name** (required)
   - Must be kebab-case (e.g., `info-card`, `feature-box`)
   - If not provided as argument, prompt for it

2. **Get configuration options**
   - Ask which features to include using checkboxes

3. **Generate files** based on selections

4. **Update plugin registration**

5. **Update CLAUDE.md** with block reference

## Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| Style variations | Yes | Add `__experimentalSkipSerialization` pattern |
| Interactivity API | No | Add `view.js` with store template |
| Server rendering | Yes | Add `render.php` for dynamic rendering |
| Static save | No | Add `save.js` (mutually exclusive with render.php) |
| Editor styles | No | Add `editor.scss` for editor-only styles |

## Generated Files

### Always Created

- `blocks/{name}/block.json` — Block metadata and configuration
- `blocks/{name}/index.js` — Block registration
- `blocks/{name}/style.css` — Base styles

### Conditional

- `blocks/{name}/render.php` — If server rendering enabled
- `blocks/{name}/save.js` — If static save enabled (no render.php)
- `blocks/{name}/view.js` — If Interactivity API enabled
- `blocks/{name}/editor.css` — If editor styles enabled

## File Templates

### block.json

```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "tgp/{block-name}",
    "version": "1.0.0",
    "title": "{Block Title}",
    "category": "design",
    "description": "A custom block.",
    "textdomain": "tgp-llms-txt",
    "keywords": [],
    "attributes": {
        "label": {
            "type": "string",
            "default": "Label"
        }
    },
    "supports": {
        "html": false,
        "anchor": true,
        "color": {
            "__experimentalSkipSerialization": true,
            "background": true,
            "text": true
        },
        "typography": {
            "fontSize": true
        },
        "spacing": {
            "padding": true
        }
    },
    "editorScript": "file:./index.js",
    "style": "file:./style.css",
    "render": "file:./render.php"
}
```

### index.js

```javascript
( function( wp ) {
    const { registerBlockType } = wp.blocks;
    const {
        useBlockProps,
        InspectorControls,
        RichText
    } = wp.blockEditor;
    const {
        PanelBody,
        ToggleControl
    } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment } = wp.element;
    const { SVG, Path } = wp.primitives;

    // Block icon
    const blockIcon = el( SVG, {
        xmlns: 'http://www.w3.org/2000/svg',
        viewBox: '0 0 24 24'
    },
        el( Path, {
            d: 'M19 6.5H5a2 2 0 00-2 2v7a2 2 0 002 2h14a2 2 0 002-2v-7a2 2 0 00-2-2z',
            fill: 'none',
            stroke: 'currentColor',
            strokeWidth: 1.5
        } )
    );

    registerBlockType( 'tgp/{block-name}', {
        icon: blockIcon,

        edit: function( props ) {
            const { attributes, setAttributes, className } = props;
            const { label } = attributes;

            const blockProps = useBlockProps( {
                className: 'wp-block-tgp-{block-name}'
            } );

            return el( Fragment, {},
                el( InspectorControls, {},
                    el( PanelBody, {
                        title: __( 'Settings', 'tgp-llms-txt' ),
                        initialOpen: true
                    },
                        // Add controls here
                    )
                ),

                el( 'div', blockProps,
                    el( RichText, {
                        tagName: 'span',
                        className: 'wp-block-tgp-{block-name}__label',
                        value: label,
                        onChange: function( value ) {
                            setAttributes( { label: value } );
                        },
                        placeholder: __( 'Label', 'tgp-llms-txt' ),
                        allowedFormats: []
                    } )
                )
            );
        },

        save: function() {
            return null;
        }
    } );
} )( window.wp );
```

### render.php

```php
<?php
/**
 * Block render template.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 *
 * @package TGP_LLMs_Txt
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes.
$label = $attributes['label'] ?? __( 'Label', 'tgp-llms-txt' );

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'wp-block-tgp-{block-name}',
) );
?>

<div <?php echo $wrapper_attributes; ?>>
    <span class="wp-block-tgp-{block-name}__label">
        <?php echo esc_html( $label ); ?>
    </span>
</div>
```

### view.js (Interactivity API)

```javascript
/**
 * Frontend interactivity.
 *
 * @package TGP_LLMs_Txt
 */

import { store, getContext } from '@wordpress/interactivity';

const { state } = store( 'tgp/{block-name}', {
    state: {
        get isDisabled() {
            const context = getContext();
            return context.isLoading;
        },
    },

    actions: {
        *handleClick() {
            const context = getContext();
            context.isLoading = true;

            try {
                // Action logic here
            } catch ( error ) {
                context.hasError = true;
            } finally {
                context.isLoading = false;
            }
        },
    },
} );
```

### style.css

```css
/**
 * Block styles.
 *
 * @package TGP_LLMs_Txt
 */

.wp-block-tgp-{block-name} {
    display: flex;
    align-items: center;
    gap: 0.5em;
}

.wp-block-tgp-{block-name}__label {
    /* Label styles */
}
```

## Plugin Registration

Add to `tgp-llms-txt.php` in the block registration section:

```php
register_block_type( __DIR__ . '/blocks/{block-name}' );
```

## CLAUDE.md Update

Add block reference to the Blocks Reference section:

```markdown
### {Block Title} (`tgp/{block-name}`)

{Description}

**Files:**
- [blocks/{block-name}/block.json](blocks/{block-name}/block.json) — Registration
- [blocks/{block-name}/index.js](blocks/{block-name}/index.js) — Editor component
- [blocks/{block-name}/render.php](blocks/{block-name}/render.php) — Server render

**Attributes:**

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `label` | `string` | `"Label"` | Block label text |
```

## Example Usage

```
User: /new-block

Claude: What would you like to name your block? (kebab-case, e.g., info-card)

User: feature-card

Claude: Which features should I include?

[x] Style variations (skip serialization pattern)
[ ] Interactivity API (view.js)
[x] Server rendering (render.php)
[ ] Static save (save.js)
[ ] Editor styles (editor.css)

User: (confirms selections)

Claude: Creating block...

Created 4 files:
  blocks/feature-card/block.json
  blocks/feature-card/index.js
  blocks/feature-card/render.php
  blocks/feature-card/style.css

Updated:
  tgp-llms-txt.php (registered block)
  CLAUDE.md (added block reference)

Your new block is ready! Add it to any post using the block inserter.
```

## Notes

- Block names must be unique within the `tgp/` namespace
- Style variation pattern requires matching logic in both index.js and render.php
- Interactivity API requires WordPress 6.5+
- Static save and server rendering are mutually exclusive
