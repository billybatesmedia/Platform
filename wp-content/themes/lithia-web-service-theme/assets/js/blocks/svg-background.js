( function( blocks, blockEditor, components, element, i18n ) {
	var registerBlockType = blocks.registerBlockType;
	var InspectorControls = blockEditor.InspectorControls;
	var InnerBlocks = blockEditor.InnerBlocks;
	var MediaUpload = blockEditor.MediaUpload;
	var MediaUploadCheck = blockEditor.MediaUploadCheck;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var RangeControl = components.RangeControl;
	var Button = components.Button;
	var Placeholder = components.Placeholder;
	var ResponsiveWrapper = components.ResponsiveWrapper;
	var createElement = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;

	var TEMPLATE = [
		[ 'core/heading', { placeholder: __( 'Add a heading', 'lithia-web-service-theme' ) } ],
		[ 'core/paragraph', { placeholder: __( 'Add content over the SVG background.', 'lithia-web-service-theme' ) } ]
	];

	function getEditorNote( attributes ) {
		if ( ! attributes.svgUrl ) {
			return '';
		}

		if ( attributes.colorMode === 'original' ) {
			return __( 'The published block renders the original SVG inline.', 'lithia-web-service-theme' );
		}

		if ( attributes.colorMode === 'single' ) {
			return __( 'The published block remaps the SVG to one Site Styles color token.', 'lithia-web-service-theme' );
		}

		return __( 'The published block remaps the SVG colors to the current Site Styles palette.', 'lithia-web-service-theme' );
	}

	registerBlockType( 'lithia/svg-background', {
		apiVersion: 2,
		title: __( 'SVG Background', 'lithia-web-service-theme' ),
		description: __( 'Reusable section block with an inline SVG background that can inherit Site Styles colors.', 'lithia-web-service-theme' ),
		icon: 'format-image',
		category: 'lithai-blocks',
		attributes: {
			align: {
				type: 'string',
				default: 'full'
			},
			tone: {
				type: 'string',
				default: 'light'
			},
			svgId: {
				type: 'number',
				default: 0
			},
			svgUrl: {
				type: 'string',
				default: ''
			},
			colorMode: {
				type: 'string',
				default: 'theme-tones'
			},
			singleColorToken: {
				type: 'string',
				default: 'primary'
			},
			position: {
				type: 'string',
				default: 'center'
			},
			opacity: {
				type: 'number',
				default: 44
			},
			scale: {
				type: 'number',
				default: 100
			},
			minHeight: {
				type: 'string',
				default: 'clamp(420px, 58vh, 760px)'
			}
		},
		supports: {
			align: [ 'full', 'wide' ],
			anchor: true,
			html: false
		},
		edit: function( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( {
				className: 'lithia-svg-background lithia-tone-' + attributes.tone + ' lw-section-' + attributes.tone + ( attributes.svgUrl ? ' lithia-svg-background--has-svg' : '' ),
				style: {
					'--lithia-svg-background-opacity': String( ( attributes.opacity || 44 ) / 100 ),
					'--lithia-svg-background-scale': String( ( attributes.scale || 100 ) / 100 ),
					'--lithia-svg-background-min-height': attributes.minHeight || 'clamp(420px, 58vh, 760px)'
				}
			} );
			var isSvgSelected = ! attributes.svgUrl || /\.svgz?(?:[?#].*)?$/i.test( attributes.svgUrl );
			var editorNote = getEditorNote( attributes );

			return createElement(
				Fragment,
				null,
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{
							title: __( 'SVG Media', 'lithia-web-service-theme' ),
							initialOpen: true
						},
						attributes.svgUrl ? createElement(
							'div',
							{ className: 'lithia-svg-background-editor-image' },
							createElement(
								ResponsiveWrapper,
								{
									naturalWidth: 1600,
									naturalHeight: 900
								},
								createElement( 'img', {
									src: attributes.svgUrl,
									alt: ''
								} )
							)
						) : null,
						createElement(
							MediaUploadCheck,
							null,
							createElement( MediaUpload, {
								allowedTypes: [ 'image' ],
								value: attributes.svgId,
								onSelect: function( media ) {
									setAttributes( {
										svgId: media && media.id ? media.id : 0,
										svgUrl: media && media.url ? media.url : ''
									} );
								},
								render: function( mediaProps ) {
									return createElement(
										Button,
										{
											variant: attributes.svgUrl ? 'secondary' : 'primary',
											onClick: mediaProps.open
										},
										attributes.svgUrl
											? __( 'Replace SVG', 'lithia-web-service-theme' )
											: __( 'Select SVG', 'lithia-web-service-theme' )
									);
								}
							} )
						),
						createElement(
							'p',
							{ className: 'lithia-svg-background__editor-note' },
							__( 'Choose an uploaded SVG from the media library. The block renders it inline on the front end for theme-aware recoloring.', 'lithia-web-service-theme' )
						),
						attributes.svgUrl ? createElement( Button, {
							isDestructive: true,
							variant: 'link',
							onClick: function() {
								setAttributes( {
									svgId: 0,
									svgUrl: ''
								} );
							}
						}, __( 'Remove SVG', 'lithia-web-service-theme' ) ) : null
					),
					createElement(
						PanelBody,
						{
							title: __( 'Appearance', 'lithia-web-service-theme' ),
							initialOpen: false
						},
						createElement( SelectControl, {
							label: __( 'Tone', 'lithia-web-service-theme' ),
							value: attributes.tone,
							options: [
								{ label: __( 'Light', 'lithia-web-service-theme' ), value: 'light' },
								{ label: __( 'Dark', 'lithia-web-service-theme' ), value: 'dark' }
							],
							onChange: function( value ) {
								setAttributes( { tone: value } );
							}
						} ),
						createElement( SelectControl, {
							label: __( 'Color Mode', 'lithia-web-service-theme' ),
							value: attributes.colorMode,
							options: [
								{ label: __( 'Theme Tones', 'lithia-web-service-theme' ), value: 'theme-tones' },
								{ label: __( 'Single Theme Color', 'lithia-web-service-theme' ), value: 'single' },
								{ label: __( 'Original SVG Colors', 'lithia-web-service-theme' ), value: 'original' }
							],
							onChange: function( value ) {
								setAttributes( { colorMode: value } );
							}
						} ),
						attributes.colorMode === 'single' && createElement( SelectControl, {
							label: __( 'Theme Color', 'lithia-web-service-theme' ),
							value: attributes.singleColorToken,
							options: [
								{ label: __( 'Primary', 'lithia-web-service-theme' ), value: 'primary' },
								{ label: __( 'Secondary', 'lithia-web-service-theme' ), value: 'secondary' },
								{ label: __( 'Accent', 'lithia-web-service-theme' ), value: 'accent' },
								{ label: __( 'Dark Background', 'lithia-web-service-theme' ), value: 'dark' },
								{ label: __( 'Light Background', 'lithia-web-service-theme' ), value: 'light' },
								{ label: __( 'Text', 'lithia-web-service-theme' ), value: 'text' }
							],
							onChange: function( value ) {
								setAttributes( { singleColorToken: value } );
							}
						} ),
						createElement( SelectControl, {
							label: __( 'Artwork Position', 'lithia-web-service-theme' ),
							value: attributes.position,
							options: [
								{ label: __( 'Center', 'lithia-web-service-theme' ), value: 'center' },
								{ label: __( 'Top', 'lithia-web-service-theme' ), value: 'top' },
								{ label: __( 'Bottom', 'lithia-web-service-theme' ), value: 'bottom' },
								{ label: __( 'Left', 'lithia-web-service-theme' ), value: 'left' },
								{ label: __( 'Right', 'lithia-web-service-theme' ), value: 'right' },
								{ label: __( 'Top Left', 'lithia-web-service-theme' ), value: 'top-left' },
								{ label: __( 'Top Right', 'lithia-web-service-theme' ), value: 'top-right' },
								{ label: __( 'Bottom Left', 'lithia-web-service-theme' ), value: 'bottom-left' },
								{ label: __( 'Bottom Right', 'lithia-web-service-theme' ), value: 'bottom-right' }
							],
							onChange: function( value ) {
								setAttributes( { position: value } );
							}
						} ),
						createElement( RangeControl, {
							label: __( 'Artwork Opacity', 'lithia-web-service-theme' ),
							value: attributes.opacity,
							onChange: function( value ) {
								setAttributes( { opacity: value || 0 } );
							},
							min: 0,
							max: 100
						} ),
						createElement( RangeControl, {
							label: __( 'Artwork Scale', 'lithia-web-service-theme' ),
							value: attributes.scale,
							onChange: function( value ) {
								setAttributes( { scale: value || 100 } );
							},
							min: 50,
							max: 200
						} ),
						createElement( TextControl, {
							label: __( 'Minimum Height', 'lithia-web-service-theme' ),
							value: attributes.minHeight,
							help: __( 'Accepts values like 560px or clamp(420px, 58vh, 760px).', 'lithia-web-service-theme' ),
							onChange: function( value ) {
								setAttributes( { minHeight: value } );
							}
						} )
					)
				),
				createElement(
					'section',
					blockProps,
					attributes.svgUrl ? createElement(
						'div',
						{
							className: 'lithia-svg-background__media',
							'aria-hidden': 'true'
						},
						createElement( 'img', {
							className: 'lithia-svg-background__media-preview',
							src: attributes.svgUrl,
							alt: ''
						} )
					) : null,
					createElement(
						'div',
						{ className: 'lithia-svg-background__inner lithia-shell' },
						createElement(
							'div',
							{ className: 'lithia-svg-background__content' },
							! attributes.svgUrl && createElement(
								Placeholder,
								{
									label: __( 'SVG Background', 'lithia-web-service-theme' ),
									instructions: __( 'Select an SVG to start using this section as a reusable background container.', 'lithia-web-service-theme' )
								},
								createElement(
									MediaUploadCheck,
									null,
									createElement( MediaUpload, {
										allowedTypes: [ 'image' ],
										value: attributes.svgId,
										onSelect: function( media ) {
											setAttributes( {
												svgId: media && media.id ? media.id : 0,
												svgUrl: media && media.url ? media.url : ''
											} );
										},
										render: function( mediaProps ) {
											return createElement(
												Button,
												{
													variant: 'primary',
													onClick: mediaProps.open
												},
												__( 'Select SVG', 'lithia-web-service-theme' )
											);
										}
									} )
								)
							),
							attributes.svgUrl && ! isSvgSelected && createElement(
								'p',
								{ className: 'lithia-svg-background__editor-note' },
								__( 'This block only renders SVG files. Replace the current media item with an uploaded SVG.', 'lithia-web-service-theme' )
							),
							editorNote ? createElement(
								'p',
								{ className: 'lithia-svg-background__editor-note' },
								editorNote
							) : null,
							createElement( InnerBlocks, {
								template: TEMPLATE,
								templateLock: false,
								renderAppender: InnerBlocks.ButtonBlockAppender
							} )
						)
					)
				)
			);
		},
		save: function() {
			return createElement( InnerBlocks.Content );
		}
	} );
} )(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.element,
	window.wp.i18n
);
