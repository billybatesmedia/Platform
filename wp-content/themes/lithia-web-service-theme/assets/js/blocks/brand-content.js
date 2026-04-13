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
	var Fragment = element.Fragment;
	var __ = i18n.__;
	var ServerSideRender = serverSideRender;

	function renderPreview( blockName, attributes ) {
		return createElement(
			'div',
			{ className: 'lithia-brand-block-preview' },
			createElement( ServerSideRender, {
				block: blockName,
				attributes: attributes
			} )
		);
	}

	function renderTonePanel( attributes, setAttributes ) {
		return createElement(
			PanelBody,
			{
				title: __( 'Style', 'lithia-web-service-theme' ),
				initialOpen: false
			},
			createElement( SelectControl, {
				label: __( 'Color Mode', 'lithia-web-service-theme' ),
				value: attributes.tone,
				options: [
					{ label: __( 'Light', 'lithia-web-service-theme' ), value: 'light' },
					{ label: __( 'Dark', 'lithia-web-service-theme' ), value: 'dark' }
				],
				onChange: function( value ) {
					setAttributes( { tone: value } );
				}
			} )
		);
	}

	registerBlockType( 'lithia/brand-intro', {
		apiVersion: 2,
		title: __( 'Brand Intro', 'lithia-web-service-theme' ),
		description: __( 'Intro section powered by Brand Content options.', 'lithia-web-service-theme' ),
		icon: 'align-wide',
		category: 'lithai-blocks',
		attributes: {
			align: { type: 'string', default: 'wide' },
			tone: { type: 'string', default: 'light' },
			eyebrow: { type: 'string', default: '' },
			heading: { type: 'string', default: '' },
			text: { type: 'string', default: '' },
			showPrimaryCta: { type: 'boolean', default: true },
			showSecondaryCta: { type: 'boolean', default: true },
			primaryLabel: { type: 'string', default: '' },
			primaryUrl: { type: 'string', default: '' },
			secondaryLabel: { type: 'string', default: '' },
			secondaryUrl: { type: 'string', default: '' }
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
				Fragment,
				null,
					createElement(
						InspectorControls,
						null,
						renderTonePanel( attributes, setAttributes ),
						createElement(
							PanelBody,
						{
							title: __( 'Intro Content', 'lithia-web-service-theme' ),
							initialOpen: true
						},
						createElement( TextControl, {
							label: __( 'Eyebrow Override', 'lithia-web-service-theme' ),
							help: __( 'Leave blank to use Brand Content > Intro Eyebrow.', 'lithia-web-service-theme' ),
							value: attributes.eyebrow,
							onChange: function( value ) {
								setAttributes( { eyebrow: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Heading Override', 'lithia-web-service-theme' ),
							help: __( 'Leave blank to use Brand Content > Intro Heading.', 'lithia-web-service-theme' ),
							value: attributes.heading,
							onChange: function( value ) {
								setAttributes( { heading: value } );
							}
						} ),
						createElement( TextareaControl, {
							label: __( 'Text Override', 'lithia-web-service-theme' ),
							help: __( 'Leave blank to use Brand Content > Intro Paragraph.', 'lithia-web-service-theme' ),
							value: attributes.text,
							onChange: function( value ) {
								setAttributes( { text: value } );
							}
						} )
					),
					createElement(
						PanelBody,
						{
							title: __( 'Buttons', 'lithia-web-service-theme' ),
							initialOpen: false
						},
						createElement( ToggleControl, {
							label: __( 'Show Primary CTA', 'lithia-web-service-theme' ),
							checked: !! attributes.showPrimaryCta,
							onChange: function( value ) {
								setAttributes( { showPrimaryCta: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Primary Label Override', 'lithia-web-service-theme' ),
							value: attributes.primaryLabel,
							onChange: function( value ) {
								setAttributes( { primaryLabel: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Primary URL Override', 'lithia-web-service-theme' ),
							value: attributes.primaryUrl,
							onChange: function( value ) {
								setAttributes( { primaryUrl: value } );
							}
						} ),
						createElement( ToggleControl, {
							label: __( 'Show Secondary CTA', 'lithia-web-service-theme' ),
							checked: !! attributes.showSecondaryCta,
							onChange: function( value ) {
								setAttributes( { showSecondaryCta: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Secondary Label Override', 'lithia-web-service-theme' ),
							value: attributes.secondaryLabel,
							onChange: function( value ) {
								setAttributes( { secondaryLabel: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Secondary URL Override', 'lithia-web-service-theme' ),
							value: attributes.secondaryUrl,
							onChange: function( value ) {
								setAttributes( { secondaryUrl: value } );
							}
						} )
					)
				),
				renderPreview( 'lithia/brand-intro', attributes )
			);
		},
		save: function() {
			return null;
		}
	} );

	registerBlockType( 'lithia/mission-statement', {
		apiVersion: 2,
		title: __( 'Mission Statement', 'lithia-web-service-theme' ),
		description: __( 'Large mission statement section powered by Brand Content.', 'lithia-web-service-theme' ),
		icon: 'format-quote',
		category: 'lithai-blocks',
		attributes: {
			align: { type: 'string', default: 'wide' },
			tone: { type: 'string', default: 'light' },
			label: { type: 'string', default: 'Mission' },
			missionText: { type: 'string', default: '' },
			imageId: { type: 'number', default: 0 },
			imageUrl: { type: 'string', default: '' },
			imageAlt: { type: 'string', default: '' }
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
				Fragment,
				null,
					createElement(
						InspectorControls,
						null,
						renderTonePanel( attributes, setAttributes ),
						createElement(
							PanelBody,
						{
							title: __( 'Mission Content', 'lithia-web-service-theme' ),
							initialOpen: true
						},
						createElement( TextControl, {
							label: __( 'Section Label', 'lithia-web-service-theme' ),
							value: attributes.label,
							onChange: function( value ) {
								setAttributes( { label: value } );
							}
						} ),
						createElement( TextareaControl, {
							label: __( 'Mission Override', 'lithia-web-service-theme' ),
							help: __( 'Leave blank to use Brand Content > Mission Statement.', 'lithia-web-service-theme' ),
							value: attributes.missionText,
							onChange: function( value ) {
								setAttributes( { missionText: value } );
							}
						} )
					),
					createElement(
						PanelBody,
						{
							title: __( 'Image', 'lithia-web-service-theme' ),
							initialOpen: false
						},
						attributes.imageUrl ? createElement(
							'div',
							{ className: 'lithia-mission-statement-editor-image' },
							createElement(
								ResponsiveWrapper,
								{
									naturalWidth: 1200,
									naturalHeight: 1500
								},
								createElement( 'img', {
									src: attributes.imageUrl,
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
										imageId: media && media.id ? media.id : 0,
										imageUrl: media && media.url ? media.url : '',
										imageAlt: media && media.alt ? media.alt : ''
									} );
								},
								allowedTypes: [ 'image' ],
								value: attributes.imageId,
								render: function( mediaProps ) {
									return createElement(
										Button,
										{
											variant: attributes.imageUrl ? 'secondary' : 'primary',
											onClick: mediaProps.open
										},
										attributes.imageUrl
											? __( 'Replace Image', 'lithia-web-service-theme' )
											: __( 'Select Image', 'lithia-web-service-theme' )
									);
								}
							} )
						),
						createElement( TextControl, {
							label: __( 'Image Alt Text', 'lithia-web-service-theme' ),
							value: attributes.imageAlt,
							onChange: function( value ) {
								setAttributes( { imageAlt: value } );
							}
						} ),
						attributes.imageUrl ? createElement( Button, {
							isDestructive: true,
							variant: 'link',
							onClick: function() {
								setAttributes( {
									imageId: 0,
									imageUrl: '',
									imageAlt: ''
								} );
							}
						}, __( 'Remove Image', 'lithia-web-service-theme' ) ) : null
					)
				),
				renderPreview( 'lithia/mission-statement', attributes )
			);
		},
		save: function() {
			return null;
		}
	} );

	registerBlockType( 'lithia/about-summary', {
		apiVersion: 2,
		title: __( 'About Summary', 'lithia-web-service-theme' ),
		description: __( 'About teaser section powered by Brand Content.', 'lithia-web-service-theme' ),
		icon: 'id-alt',
		category: 'lithai-blocks',
		attributes: {
			align: { type: 'string', default: 'wide' },
			tone: { type: 'string', default: 'light' },
			eyebrow: { type: 'string', default: 'About Us' },
			heading: { type: 'string', default: 'A closer look at the business' },
			text: { type: 'string', default: '' },
			imageId: { type: 'number', default: 0 },
			imageUrl: { type: 'string', default: '' },
			imageAlt: { type: 'string', default: '' },
			showButton: { type: 'boolean', default: true },
			buttonLabel: { type: 'string', default: '' },
			buttonUrl: { type: 'string', default: '' }
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
				Fragment,
				null,
					createElement(
						InspectorControls,
						null,
						renderTonePanel( attributes, setAttributes ),
						createElement(
							PanelBody,
						{
							title: __( 'Summary Content', 'lithia-web-service-theme' ),
							initialOpen: true
						},
						createElement( TextControl, {
							label: __( 'Eyebrow', 'lithia-web-service-theme' ),
							value: attributes.eyebrow,
							onChange: function( value ) {
								setAttributes( { eyebrow: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Heading', 'lithia-web-service-theme' ),
							value: attributes.heading,
							onChange: function( value ) {
								setAttributes( { heading: value } );
							}
						} ),
						createElement( TextareaControl, {
							label: __( 'About Summary Override', 'lithia-web-service-theme' ),
							help: __( 'Leave blank to use Brand Content > About Summary.', 'lithia-web-service-theme' ),
							value: attributes.text,
							onChange: function( value ) {
								setAttributes( { text: value } );
							}
						} )
					),
					createElement(
						PanelBody,
						{
							title: __( 'Image', 'lithia-web-service-theme' ),
							initialOpen: false
						},
						attributes.imageUrl ? createElement(
							'div',
							{ className: 'lithia-about-summary-editor-image' },
							createElement(
								ResponsiveWrapper,
								{
									naturalWidth: 1200,
									naturalHeight: 900
								},
								createElement( 'img', {
									src: attributes.imageUrl,
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
										imageId: media && media.id ? media.id : 0,
										imageUrl: media && media.url ? media.url : '',
										imageAlt: media && media.alt ? media.alt : ''
									} );
								},
								allowedTypes: [ 'image' ],
								value: attributes.imageId,
								render: function( mediaProps ) {
									return createElement(
										Button,
										{
											variant: attributes.imageUrl ? 'secondary' : 'primary',
											onClick: mediaProps.open
										},
										attributes.imageUrl
											? __( 'Replace Image', 'lithia-web-service-theme' )
											: __( 'Select Image', 'lithia-web-service-theme' )
									);
								}
							} )
						),
						createElement( TextControl, {
							label: __( 'Image Alt Text', 'lithia-web-service-theme' ),
							value: attributes.imageAlt,
							onChange: function( value ) {
								setAttributes( { imageAlt: value } );
							}
						} ),
						attributes.imageUrl ? createElement( Button, {
							isDestructive: true,
							variant: 'link',
							onClick: function() {
								setAttributes( {
									imageId: 0,
									imageUrl: '',
									imageAlt: ''
								} );
							}
						}, __( 'Remove Image', 'lithia-web-service-theme' ) ) : null
					),
					createElement(
						PanelBody,
						{
							title: __( 'Button', 'lithia-web-service-theme' ),
							initialOpen: false
						},
						createElement( ToggleControl, {
							label: __( 'Show Button', 'lithia-web-service-theme' ),
							checked: !! attributes.showButton,
							onChange: function( value ) {
								setAttributes( { showButton: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Button Label Override', 'lithia-web-service-theme' ),
							value: attributes.buttonLabel,
							onChange: function( value ) {
								setAttributes( { buttonLabel: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Button URL Override', 'lithia-web-service-theme' ),
							value: attributes.buttonUrl,
							onChange: function( value ) {
								setAttributes( { buttonUrl: value } );
							}
						} )
					)
				),
				renderPreview( 'lithia/about-summary', attributes )
			);
		},
		save: function() {
			return null;
		}
	} );

	registerBlockType( 'lithia/brand-cta-pair', {
		apiVersion: 2,
		title: __( 'Brand CTA Pair', 'lithia-web-service-theme' ),
		description: __( 'Reusable primary and secondary CTA buttons powered by Brand Content.', 'lithia-web-service-theme' ),
		icon: 'button',
		category: 'lithai-blocks',
		attributes: {
			align: { type: 'string', default: 'wide' },
			tone: { type: 'string', default: 'light' },
			showPrimaryCta: { type: 'boolean', default: true },
			showSecondaryCta: { type: 'boolean', default: true },
			primaryLabel: { type: 'string', default: '' },
			primaryUrl: { type: 'string', default: '' },
			secondaryLabel: { type: 'string', default: '' },
			secondaryUrl: { type: 'string', default: '' }
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
				Fragment,
				null,
					createElement(
						InspectorControls,
						null,
						renderTonePanel( attributes, setAttributes ),
						createElement(
							PanelBody,
						{
							title: __( 'Buttons', 'lithia-web-service-theme' ),
							initialOpen: true
						},
						createElement( ToggleControl, {
							label: __( 'Show Primary CTA', 'lithia-web-service-theme' ),
							checked: !! attributes.showPrimaryCta,
							onChange: function( value ) {
								setAttributes( { showPrimaryCta: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Primary Label Override', 'lithia-web-service-theme' ),
							value: attributes.primaryLabel,
							onChange: function( value ) {
								setAttributes( { primaryLabel: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Primary URL Override', 'lithia-web-service-theme' ),
							value: attributes.primaryUrl,
							onChange: function( value ) {
								setAttributes( { primaryUrl: value } );
							}
						} ),
						createElement( ToggleControl, {
							label: __( 'Show Secondary CTA', 'lithia-web-service-theme' ),
							checked: !! attributes.showSecondaryCta,
							onChange: function( value ) {
								setAttributes( { showSecondaryCta: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Secondary Label Override', 'lithia-web-service-theme' ),
							value: attributes.secondaryLabel,
							onChange: function( value ) {
								setAttributes( { secondaryLabel: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Secondary URL Override', 'lithia-web-service-theme' ),
							value: attributes.secondaryUrl,
							onChange: function( value ) {
								setAttributes( { secondaryUrl: value } );
							}
						} )
					)
				),
				renderPreview( 'lithia/brand-cta-pair', attributes )
			);
		},
		save: function() {
			return null;
		}
	} );

	registerBlockType( 'lithia/contact-details', {
		apiVersion: 2,
		title: __( 'Contact Details', 'lithia-web-service-theme' ),
		description: __( 'Business address and contact details pulled from the options page.', 'lithia-web-service-theme' ),
		icon: 'location-alt',
		category: 'lithai-blocks',
		attributes: {
			align: { type: 'string', default: 'wide' },
			tone: { type: 'string', default: 'light' },
			heading: { type: 'string', default: 'Contact Details' },
			intro: { type: 'string', default: '' },
			showHours: { type: 'boolean', default: true },
			showNotice: { type: 'boolean', default: true }
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
				Fragment,
				null,
				createElement(
					InspectorControls,
					null,
					renderTonePanel( attributes, setAttributes ),
					createElement(
						PanelBody,
						{
							title: __( 'Content', 'lithia-web-service-theme' ),
							initialOpen: true
						},
						createElement( TextControl, {
							label: __( 'Heading', 'lithia-web-service-theme' ),
							value: attributes.heading,
							onChange: function( value ) {
								setAttributes( { heading: value } );
							}
						} ),
						createElement( TextareaControl, {
							label: __( 'Intro', 'lithia-web-service-theme' ),
							help: __( 'Optional supporting text above the address and contact details.', 'lithia-web-service-theme' ),
							value: attributes.intro,
							onChange: function( value ) {
								setAttributes( { intro: value } );
							}
						} ),
						createElement( ToggleControl, {
							label: __( 'Show Hours', 'lithia-web-service-theme' ),
							checked: !! attributes.showHours,
							onChange: function( value ) {
								setAttributes( { showHours: value } );
							}
						} ),
						createElement( ToggleControl, {
							label: __( 'Show Booking Notice', 'lithia-web-service-theme' ),
							checked: !! attributes.showNotice,
							onChange: function( value ) {
								setAttributes( { showNotice: value } );
							}
						} )
					)
				),
				renderPreview( 'lithia/contact-details', attributes )
			);
		},
		save: function() {
			return null;
		}
	} );

	registerBlockType( 'lithia/contact-form', {
		apiVersion: 2,
		title: __( 'Contact Form', 'lithia-web-service-theme' ),
		description: __( 'Managed contact form for the Contact page.', 'lithia-web-service-theme' ),
		icon: 'email',
		category: 'lithai-blocks',
		attributes: {
			align: { type: 'string', default: 'wide' },
			tone: { type: 'string', default: 'dark' },
			heading: { type: 'string', default: 'Send a Message' },
			intro: { type: 'string', default: '' }
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
				Fragment,
				null,
				createElement(
					InspectorControls,
					null,
					renderTonePanel( attributes, setAttributes ),
					createElement(
						PanelBody,
						{
							title: __( 'Content', 'lithia-web-service-theme' ),
							initialOpen: true
						},
						createElement( TextControl, {
							label: __( 'Heading', 'lithia-web-service-theme' ),
							value: attributes.heading,
							onChange: function( value ) {
								setAttributes( { heading: value } );
							}
						} ),
						createElement( TextareaControl, {
							label: __( 'Intro', 'lithia-web-service-theme' ),
							help: __( 'Optional supporting text above the managed form.', 'lithia-web-service-theme' ),
							value: attributes.intro,
							onChange: function( value ) {
								setAttributes( { intro: value } );
							}
						} )
					)
				),
				renderPreview( 'lithia/contact-form', attributes )
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
