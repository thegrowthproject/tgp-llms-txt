( function( wp ) {
	const { registerBlockType } = wp.blocks;
	const {
		useBlockProps,
		InspectorControls,
		RichText
	} = wp.blockEditor;
	const {
		PanelBody,
		ToggleControl,
		__experimentalToggleGroupControl: ToggleGroupControl,
		__experimentalToggleGroupControlOption: ToggleGroupControlOption
	} = wp.components;
	const { __ } = wp.i18n;
	const { createElement: el, Fragment } = wp.element;

	// Copy icon SVG
	const copyIcon = el( 'svg', {
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
		el( 'rect', { x: 9, y: 9, width: 13, height: 13, rx: 2, ry: 2 } ),
		el( 'path', { d: 'M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1' } )
	);

	// Block icon (larger version)
	const blockIcon = el( 'svg', {
		xmlns: 'http://www.w3.org/2000/svg',
		width: 24,
		height: 24,
		viewBox: '0 0 24 24',
		fill: 'none',
		stroke: 'currentColor',
		strokeWidth: 2
	},
		el( 'rect', { x: 9, y: 9, width: 13, height: 13, rx: 2, ry: 2 } ),
		el( 'path', { d: 'M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1' } )
	);

	registerBlockType( 'tgp/copy-button', {
		icon: blockIcon,

		edit: function( props ) {
			const { attributes, setAttributes } = props;
			const { label, showIcon, width } = attributes;

			// Width class for wrapper
			const wrapperClass = width
				? 'wp-block-button has-custom-width wp-block-button__width-' + width
				: 'wp-block-button';

			const blockProps = useBlockProps( {
				className: 'wp-block-button__link wp-element-button tgp-copy-btn'
			} );

			return el( Fragment, {},
				// Inspector Controls (Sidebar)
				el( InspectorControls, {},
					el( PanelBody, {
						title: __( 'Settings', 'tgp-llms-txt' ),
						initialOpen: true
					},
						el( ToggleGroupControl, {
							__nextHasNoMarginBottom: true,
							label: __( 'Width', 'tgp-llms-txt' ),
							value: width ? String( width ) : undefined,
							onChange: function( value ) {
								setAttributes( { width: value ? Number( value ) : undefined } );
							},
							isBlock: true
						},
							el( ToggleGroupControlOption, { key: '25', value: '25', label: '25%' } ),
							el( ToggleGroupControlOption, { key: '50', value: '50', label: '50%' } ),
							el( ToggleGroupControlOption, { key: '75', value: '75', label: '75%' } ),
							el( ToggleGroupControlOption, { key: '100', value: '100', label: '100%' } )
						),
						el( ToggleControl, {
							__nextHasNoMarginBottom: true,
							label: __( 'Show Icon', 'tgp-llms-txt' ),
							checked: showIcon,
							onChange: function( value ) {
								setAttributes( { showIcon: value } );
							}
						} )
					)
				),

				// Block Preview
				el( 'div', { className: wrapperClass },
					el( 'button', blockProps,
						showIcon && el( 'span', { className: 'tgp-btn-icon' }, copyIcon ),
						el( RichText, {
							tagName: 'span',
							className: 'tgp-btn-text',
							value: label,
							onChange: function( value ) {
								setAttributes( { label: value } );
							},
							placeholder: __( 'Copy for LLM', 'tgp-llms-txt' ),
							allowedFormats: []
						} )
					)
				)
			);
		},

		save: function() {
			return null;
		}
	} );
} )( window.wp );
