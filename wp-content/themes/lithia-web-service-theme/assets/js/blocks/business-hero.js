( function( blocks, blockEditor, components, element, i18n, serverSideRender ) {
	var registerBlockType = blocks.registerBlockType;
	var InspectorControls = blockEditor.InspectorControls;
	var MediaUpload = blockEditor.MediaUpload;
	var MediaUploadCheck = blockEditor.MediaUploadCheck;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ToggleControl = components.ToggleControl;
	var Button = components.Button;
	var ResponsiveWrapper = components.ResponsiveWrapper;
	var createElement = element.createElement;
	var __ = i18n.__;
	var ServerSideRender = serverSideRender;

	registerBlockType( 'lithia/business-hero', {
		apiVersion: 2,
		title: __( 'Business Hero', 'lithia-web-service-theme' ),
		description: __( 'Full-width hero with a background image or YouTube video and business details from the options page.', 'lithia-web-service-theme' ),
		icon: 'cover-image',
		category: 'lithai-blocks',
		attributes: {
			align: {
				type: 'string',
				default: 'full'
			},
			eyebrow: {
				type: 'string',
				default: 'Service-based business'
			},
			useBusinessName: {
				type: 'boolean',
				default: true
			},
			headline: {
				type: 'string',
				default: ''
			},
			useCity: {
				type: 'boolean',
				default: true
			},
			subheading: {
				type: 'string',
				default: ''
			},
			text: {
				type: 'string',
				default: 'Use this hero to introduce your business and drive visitors into the booking flow.'
			},
			buttonText: {
				type: 'string',
				default: 'Book Appointment'
			},
			buttonUrl: {
				type: 'string',
				default: '/book-appointment/'
			},
			backgroundImageId: {
				type: 'number',
				default: 0
			},
			backgroundImageUrl: {
				type: 'string',
				default: ''
			},
			backgroundType: {
				type: 'string',
				default: 'image'
			},
			youtubeUrl: {
				type: 'string',
				default: ''
			},
			tone: {
				type: 'string',
				default: 'dark'
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

			return createElement(
				element.Fragment,
				null,
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{
							title: __( 'Style', 'lithia-web-service-theme' ),
							initialOpen: false
						},
						createElement( SelectControl, {
							label: __( 'Color Mode', 'lithia-web-service-theme' ),
							value: attributes.tone,
							options: [
								{ label: __( 'Dark', 'lithia-web-service-theme' ), value: 'dark' },
								{ label: __( 'Light', 'lithia-web-service-theme' ), value: 'light' }
							],
							onChange: function( value ) {
								setAttributes( { tone: value } );
							}
						} )
					),
					createElement(
						PanelBody,
						{
							title: __( 'Hero Content', 'lithia-web-service-theme' ),
							initialOpen: true
						},
						createElement( TextControl, {
							label: __( 'Eyebrow', 'lithia-web-service-theme' ),
							value: attributes.eyebrow,
							onChange: function( value ) {
								setAttributes( { eyebrow: value } );
							}
						} ),
						createElement( ToggleControl, {
							label: __( 'Use Business Name for headline', 'lithia-web-service-theme' ),
							checked: !! attributes.useBusinessName,
							onChange: function( value ) {
								setAttributes( { useBusinessName: value } );
							}
						} ),
						! attributes.useBusinessName && createElement( TextControl, {
							label: __( 'Headline', 'lithia-web-service-theme' ),
							value: attributes.headline,
							onChange: function( value ) {
								setAttributes( { headline: value } );
							}
						} ),
						createElement( ToggleControl, {
							label: __( 'Use City for subheading', 'lithia-web-service-theme' ),
							checked: !! attributes.useCity,
							onChange: function( value ) {
								setAttributes( { useCity: value } );
							}
						} ),
						! attributes.useCity && createElement( TextControl, {
							label: __( 'Subheading', 'lithia-web-service-theme' ),
							value: attributes.subheading,
							onChange: function( value ) {
								setAttributes( { subheading: value } );
							}
						} ),
						createElement( TextareaControl, {
							label: __( 'Text', 'lithia-web-service-theme' ),
							value: attributes.text,
							onChange: function( value ) {
								setAttributes( { text: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Button Text', 'lithia-web-service-theme' ),
							value: attributes.buttonText,
							onChange: function( value ) {
								setAttributes( { buttonText: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Button URL', 'lithia-web-service-theme' ),
							value: attributes.buttonUrl,
							onChange: function( value ) {
								setAttributes( { buttonUrl: value } );
							}
						} )
					),
					createElement(
						PanelBody,
						{
							title: __( 'Background Media', 'lithia-web-service-theme' ),
							initialOpen: true
						},
						createElement( SelectControl, {
							label: __( 'Background Type', 'lithia-web-service-theme' ),
							value: attributes.backgroundType,
							options: [
								{ label: __( 'Image', 'lithia-web-service-theme' ), value: 'image' },
								{ label: __( 'YouTube Video', 'lithia-web-service-theme' ), value: 'youtube' }
							],
							onChange: function( value ) {
								setAttributes( { backgroundType: value } );
							}
						} ),
						attributes.backgroundType === 'youtube' && createElement( TextControl, {
							label: __( 'YouTube URL', 'lithia-web-service-theme' ),
							value: attributes.youtubeUrl,
							help: __( 'Paste a YouTube watch, share, or embed URL. The image below stays available as a fallback background.', 'lithia-web-service-theme' ),
							onChange: function( value ) {
								setAttributes( { youtubeUrl: value } );
							}
						} ),
						attributes.backgroundImageUrl ? createElement(
							'div',
							{ className: 'lithia-business-hero-editor-image' },
							createElement(
								ResponsiveWrapper,
								{
									naturalWidth: 1600,
									naturalHeight: 900
								},
								createElement( 'img', {
									src: attributes.backgroundImageUrl,
									alt: ''
								} )
							)
						) : null,
						createElement(
							MediaUploadCheck,
							null,
							createElement( MediaUpload, {
								onSelect: function( media ) {
									setAttributes( {
										backgroundImageId: media && media.id ? media.id : 0,
										backgroundImageUrl: media && media.url ? media.url : ''
									} );
								},
								allowedTypes: [ 'image' ],
								value: attributes.backgroundImageId,
								render: function( mediaProps ) {
									return createElement(
										Button,
										{
											variant: attributes.backgroundImageUrl ? 'secondary' : 'primary',
											onClick: mediaProps.open
										},
										attributes.backgroundImageUrl
											? (
												attributes.backgroundType === 'youtube'
													? __( 'Replace Fallback Image', 'lithia-web-service-theme' )
													: __( 'Replace Background Image', 'lithia-web-service-theme' )
											)
											: (
												attributes.backgroundType === 'youtube'
													? __( 'Select Fallback Image', 'lithia-web-service-theme' )
													: __( 'Select Background Image', 'lithia-web-service-theme' )
											)
									);
								}
							} )
						),
						attributes.backgroundImageUrl ? createElement( Button, {
							isDestructive: true,
							variant: 'link',
							onClick: function() {
								setAttributes( {
									backgroundImageId: 0,
									backgroundImageUrl: ''
								} );
							}
						}, __( 'Remove Image', 'lithia-web-service-theme' ) ) : null
					)
				),
				createElement(
					'div',
					{ className: 'lithia-business-hero-editor-preview' },
					createElement( ServerSideRender, {
						block: 'lithia/business-hero',
						attributes: attributes
					} )
				)
			);
		},
		save: function() {
			return null;
		}
	} );
} )(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.element,
	window.wp.i18n,
	window.wp.serverSideRender
);
